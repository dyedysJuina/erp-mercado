<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\FormaPagamento;
use App\Models\Loja;
use App\Models\PdvCaixaMovimento;
use App\Models\PdvDevolucao;
use App\Models\PdvDevolucaoItem;
use App\Models\PdvVenda;
use App\Models\ProdutoVariacao;
use App\Models\FiscalDocumento;
use App\Services\NfceService;
use App\Services\PdvSaleService;
use App\Support\BrazilianNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VendaManager extends Component
{
    public ?int $vendaId = null;

    public ?int $caixaAberturaId = null;

    public string $passo = 'inicio';

    public string $loja_id = '';

    public function mount(): void
    {
        $user = auth()->user();
        if ($user->loja_id) {
            $this->loja_id = (string)$user->loja_id;
        }

        // Restaura caixa aberto se existir
        $abertura = DB::table('pdv_caixas_aberturas')
            ->join('pdv_caixas', 'pdv_caixas.id', '=', 'pdv_caixas_aberturas.caixa_id')
            ->where('pdv_caixas_aberturas.usuario_id', $user->id)
            ->where('pdv_caixas_aberturas.status', 'aberto')
            ->when($user->loja_id, fn($q) => $q->where('pdv_caixas.loja_id', $user->loja_id))
            ->orderBy('pdv_caixas_aberturas.id', 'desc')
            ->first(['pdv_caixas_aberturas.id', 'pdv_caixas.loja_id']);

        if ($abertura) {
            $this->caixaAberturaId = (int)$abertura->id;
            $this->loja_id = (string)$abertura->loja_id;
            $this->passo = 'venda';
        }
    }

    public string $valorAbertura = '0,00';

    // ─── Sangria / Suprimento ───
    public bool $movModalOpen = false;
    public string $movTipo = 'sangria';
    public string $movValor = '';
    public string $movMotivo = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public string $cliente_id = '';

    public string $clienteNome = '';

    public string $buscaCliente = '';

    public string $buscaProduto = '';

    public string $codigoBarrasLido = '';

    public string $qtdBusca = '1';

    public array $carrinho = [];

    public string $desconto = '0,00';

    public string $acrescimo = '0,00';

    public string $forma_pagamento_id = '';

    // ─── Múltiplos Pagamentos ───
    public array $pagamentos = [];
    public string $valorPagamentoAtual = '0,00';
    public bool $pagamentoTipoEditando = false;

    public string $valorRecebido = '0,00';

    public string $troco = '0,00';
    // ─── Cancelamento ───
    public bool $cancelModalOpen = false;
    public string $cancelTipo = 'item'; // 'item' | 'venda'
    public string $cancelMotivo = '';
    public bool $cancelListaOpen = false;
    public array $cancelItensSelecionados = [];

    // ─── Desconto ───
    public string $descontoTipo = 'valor'; // 'valor' | 'percentual'
    public string $descontoPct = '0,00';
    public string $acrescimoPct = '0,00';

    // ─── Estorno ───
    public bool $estornoModalOpen = false;
    public string $estornoMotivo = '';

    // ─── Devolução ───
    public bool $devModalOpen = false;
    public string $devBuscaVendaId = '';
    public ?array $devVenda = null;
    public array $devItensSelecionados = [];
    public string $devMotivo = '';
    public string $devMensagem = '';

    // ─── Dashboard ───
    public bool $dashboardOpen = false;

    public function abrirDashboard(): void { $this->dashboardOpen = true; }

    #[Computed]
    public function dashboardVendasHoje(): array
    {
        $hoje = now()->startOfDay();
        $vendas = PdvVenda::where('created_at', '>=', $hoje);
        return [
            'total' => (float) $vendas->clone()->where('status', 'concluida')->sum('total'),
            'qtd' => $vendas->clone()->where('status', 'concluida')->count(),
            'canceladas' => $vendas->clone()->where('status', 'cancelada')->count(),
        ];
    }

    #[Computed]
    public function dashboardTicketMedio(): float
    {
        $hoje = now()->startOfDay();
        $qtd = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $hoje)->count();
        $total = PdvVenda::where('status', 'concluida')->where('created_at', '>=', $hoje)->sum('total');
        return $qtd > 0 ? $total / $qtd : 0;
    }

    #[Computed]
    public function dashboardVendasPorHora(): array
    {
        $hoje = now()->startOfDay();
        $rows = DB::table('pdv_vendas')
            ->where('status', 'concluida')
            ->where('created_at', '>=', $hoje)
            ->selectRaw('HOUR(created_at) as hora, COUNT(*) as qtd, SUM(total) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->get();

        $horas = [];
        for ($h = 6; $h <= 22; $h++) {
            $horas[] = [
                'hora' => $h . 'h',
                'qtd' => 0,
                'total' => 0,
            ];
        }
        foreach ($rows as $r) {
            if ($r->hora >= 6 && $r->hora <= 22) {
                $horas[$r->hora - 6]['qtd'] = (int) $r->qtd;
                $horas[$r->hora - 6]['total'] = (float) $r->total;
            }
        }
        return $horas;
    }

    public string $filtroHistorico = '';

    // ─── Fechamento de Caixa ───
    public bool $closeModalOpen = false;
    public string $closeValorEncontrado = '';
    public ?float $closeDiferenca = null;

    public function fecharCaixa(): void
    {
        if (!$this->caixaAberturaId) {
            $this->addError('loja_id', 'Caixa não está aberto.');
            return;
        }

        $abertura = PdvCaixaAbertura::lockForUpdate()->find($this->caixaAberturaId);
        if (!$abertura || $abertura->usuario_id !== auth()->id()) {
            $this->addError('loja_id', 'Caixa não pertence ao usuário atual.');
            return;
        }

        $valorEncontrado = (float) str_replace(',', '.', str_replace('.', '', $this->closeValorEncontrado ?? '0'));
        $dados = $this->dadosFechamento();

        DB::transaction(function () use ($abertura, $valorEncontrado, $dados) {
            $abertura->update([
                'status' => 'fechado',
                'valor_fechamento_informado' => $valorEncontrado,
                'valor_fechamento_sistema' => $dados['esperado'],
                'diferenca' => $dados['diferenca'],
                'fechado_at' => now(),
            ]);
        });

        $this->caixaAberturaId = null;
        $this->closeModalOpen = false;
        $this->passo = 'inicio';
        $this->resetSale();
        $this->toast('Caixa fechado com sucesso!');
    }

    #[Computed]
    public function dadosFechamento(): array
    {
        $abertura = PdvCaixaAbertura::whereKey($this->caixaAberturaId)->value('valor_abertura') ?? 0;
        $vendas = PdvVenda::where('caixa_abertura_id', $this->caixaAberturaId)->where('status', 'concluida')->sum('total');
        $sangrias = $this->totalSangrias;
        $suprimentos = $this->totalSuprimentos;
        $esperado = (float)$abertura + (float)$vendas + $suprimentos - $sangrias;

        $encontrado = $this->closeValorEncontrado !== ''
            ? (float) str_replace(',', '.', str_replace('.', '', $this->closeValorEncontrado))
            : 0;

        return [
            'abertura' => (float)$abertura,
            'vendas' => (float) $vendas,
            'sangrias' => $sangrias,
            'suprimentos' => $suprimentos,
            'esperado' => $esperado,
            'encontrado' => $encontrado,
            'diferenca' => $encontrado - $esperado,
        ];
    }
    public ?string $nfceStatus = null;
    public ?int $nfceDocumentoId = null;
    public float $totalFinalizado = 0;

    public function iniciarVenda(PdvSaleService $service): void
    {
        $this->validate(['loja_id' => ['required', 'integer', 'exists:lojas,id']]);

        if ($this->caixaAberturaId) {
            $this->passo = 'venda';
            return;
        }

        $this->caixaAberturaId = $service->openCash((int)$this->loja_id, (int)auth()->id(), $this->valorAbertura);
        $this->passo = 'venda';
    }

    public function adicionarProduto(int $variacaoId): void
    {
        $result = $this->productsForCurrentStore()
            ->where('produto_variacoes.id', $variacaoId)
            ->first();

        if (!$result || !$result->preco_venda || $result->preco_venda <= 0) return;

        $qtd = max(0.001, $this->decimal($this->qtdBusca));

        $idx = array_search($variacaoId, array_column($this->carrinho, 'variacao_id'));
        if ($idx !== false) {
            $this->carrinho[$idx]['quantidade'] += $qtd;
        } else {
            $codBarras = DB::table('produto_codigos_barras')
                ->where('produto_variacao_id', $result->id)
                ->where('principal', true)
                ->value('codigo');

            $this->carrinho[] = [
                'variacao_id' => $result->id,
                'nome' => $result->nome_completo,
                'marca' => $result->marca?->nome ?? '',
                'unidade' => $result->unidadeMedida?->sigla ?? 'UN',
                'estoque' => $result->estoque_disponivel ?? 0,
                'sku' => $result->sku ?? '',
                'codigo_barras' => $codBarras ?? '',
                'foto_url' => $result->foto_url ?? '',
                'preco' => (float)$result->preco_venda,
                'quantidade' => $qtd,
            ];
        }
        $this->buscaProduto = '';
        $this->codigoBarrasLido = '';
        $this->qtdBusca = '1';
        $this->dispatch('produto-adicionado');

        if ($result && (float)$result->estoque_disponivel <= 3 && (float)$result->estoque_disponivel > 0) {
            $this->dispatch('estoque-baixo', nome: $result->nome_completo, estoque: (float)$result->estoque_disponivel);
        }
    }

    public function adicionarProdutoPorCodigo(string $codigo): void
    {
        $barcode = DB::table('produto_codigos_barras')
            ->where('codigo', $codigo)
            ->first();

        if ($barcode) {
            $this->adicionarProduto($barcode->produto_variacao_id);
            $this->codigoBarrasLido = 'ok';
            return;
        }

        $this->codigoBarrasLido = 'nao_encontrado';
        // Se não achou por código, busca por texto
        $this->buscaProduto = $codigo;
    }

    public function removerItem(int $index): void
    {
        if (isset($this->carrinho[$index])) {
            unset($this->carrinho[$index]);
            $this->carrinho = array_values($this->carrinho);
        }
    }

    public function removerCliente(): void
    {
        $this->cliente_id = '';
        $this->clienteNome = '';
        $this->buscaCliente = '';
    }

    public function selecionarCliente(int $id): void
    {
        $cliente = Cliente::find($id);
        if ($cliente) {
            $this->cliente_id = (string)$cliente->id;
            $this->clienteNome = $cliente->nome;
            $this->buscaCliente = '';
        }
    }

    public function irPagamento(): void
    {
        if (empty($this->carrinho)) return;
        $this->pagamentos = [];
        $this->forma_pagamento_id = '';
        $this->valorRecebido = '0,00';
        $this->passo = 'pagamento';
    }

    public function adicionarPagamento(int $formaId): void
    {
        $fp = FormaPagamento::find($formaId);
        if (!$fp) return;

        // Se já existe pagamento cobrindo o total, limpa para trocar
        $restante = $this->total - collect($this->pagamentos)->sum('valor');
        if ($restante <= 0.01) {
            $this->pagamentos = [];
            $restante = $this->total;
        }

        if ($fp->tipo === 'dinheiro') {
            $valor = $this->decimal($this->valorRecebido);
            if ($valor <= 0) $valor = $restante;
        } else {
            $valor = $restante;
        }

        if ($valor <= 0) return;

        $this->pagamentos[] = [
            'forma_pagamento_id' => $formaId,
            'nome' => $fp->nome,
            'tipo' => $fp->tipo,
            'valor' => $valor,
        ];

        $this->forma_pagamento_id = (string)$formaId;
        $this->valorRecebido = '0,00';
    }

    public function removerPagamento(int $idx): void
    {
        if (isset($this->pagamentos[$idx])) {
            unset($this->pagamentos[$idx]);
            $this->pagamentos = array_values($this->pagamentos);
        }
    }

    public function atualizarValorPagamento(int $idx, float $valor): void
    {
        if (isset($this->pagamentos[$idx])) {
            $this->pagamentos[$idx]['valor'] = $valor;
        }
    }

    #[Computed]
    public function totalPago(): float
    {
        return collect($this->pagamentos)->sum('valor');
    }

    #[Computed]
    public function restantePagar(): float
    {
        return max(0, $this->total - $this->totalPago);
    }

    public function voltarVenda(): void
    {
        $this->passo = 'venda';
    }

    public function emitirNfce(NfceService $service): void
    {
        if ($this->nfceDocumentoId) {
            $this->addError('nfce', 'NFC-e já foi emitida para esta venda.');
            return;
        }

        $venda = PdvVenda::with(['itens.variacao', 'pagamentos.formaPagamento', 'cliente.enderecos.cidade.estado'])->find($this->vendaId);
        if (!$venda) { $this->nfceStatus = 'erro'; return; }

        if ($venda->status !== 'concluida') {
            $this->addError('nfce', 'Venda não está concluída.');
            return;
        }

        if ((int)$venda->loja_id !== (int)$this->loja_id) {
            $this->addError('nfce', 'Venda não pertence a esta loja.');
            return;
        }

        $loja = Loja::with('cidade.estado')->find((int)$this->loja_id);
        if (!$loja) { $this->nfceStatus = 'erro'; return; }

        try {
            $doc = $service->emitir($venda, $loja);
            $this->nfceDocumentoId = $doc->id;
            $this->nfceStatus = $doc->status;
        } catch (\Exception $e) {
            $this->nfceStatus = 'erro';
            $this->addError('nfce', $e->getMessage());
        }
    }

    public function finalizar(PdvSaleService $service): void
    {
        $totalPago = $this->totalPago;
        $diferenca = abs($totalPago - $this->total);

        $this->validate([
            'loja_id' => ['required', 'integer', 'exists:lojas,id'],
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
        ]);

        if (count($this->pagamentos) === 0) {
            throw ValidationException::withMessages(['forma_pagamento_id' => 'Adicione pelo menos uma forma de pagamento.']);
        }

        if ($diferenca > 0.01) {
            throw ValidationException::withMessages(['forma_pagamento_id' => 'O total dos pagamentos difere do valor da venda.']);
        }

        if (! $this->caixaAberturaId || ! auth()->id()) {
            throw ValidationException::withMessages(['caixa' => 'O caixa desta venda não está aberto.']);
        }

        if (!$this->verificarAutorizacaoCaixa()) {
            throw ValidationException::withMessages(['caixa' => 'Caixa não pertence ao usuário atual.']);
        }

        $result = $service->finalizeMulti(
            (int) $this->loja_id,
            $this->caixaAberturaId,
            (int) auth()->id(),
            $this->cliente_id !== '' ? (int) $this->cliente_id : null,
            $this->carrinho,
            $this->desconto,
            $this->acrescimo,
            $this->pagamentos,
        );

        $this->vendaId = $result['id'];
        $this->totalFinalizado = $result['total'];
        $this->troco = number_format($result['change'], 2, ',', '.');
        $this->passo = 'finalizada';

        // Verifica estoque baixo apenas nos itens vendidos
        $userId = auth()->id();
        $cartVariacaoIds = collect($this->carrinho)->pluck('variacao_id')->toArray();
        $itensBaixo = \App\Models\EstoqueSaldo::where('loja_id', (int)$this->loja_id)
            ->whereIn('produto_variacao_id', $cartVariacaoIds)
            ->where('quantidade_atual', '>', 0)
            ->whereColumn('quantidade_atual', '<=', 'estoque_minimo')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_saldos.produto_variacao_id')
            ->select('produto_variacoes.nome_completo', 'estoque_saldos.quantidade_atual', 'estoque_saldos.estoque_minimo')
            ->get();
        foreach ($itensBaixo as $ib) {
            $existe = \App\Models\Notificacao::where('usuario_id', $userId)
                ->where('titulo', 'Estoque Baixo')
                ->where('mensagem', 'like', "%{$ib->nome_completo}%")
                ->where('created_at', '>=', now()->subHours(6))
                ->exists();
            if (!$existe) {
                \App\Models\Notificacao::create([
                    'usuario_id' => $userId,
                    'canal' => 'sistema',
                    'titulo' => 'Estoque Baixo',
                    'mensagem' => "{$ib->nome_completo} - Estoque atual: {$ib->quantidade_atual}, Minimo: {$ib->estoque_minimo}",
                    'status' => 'pendente',
                ]);
                $this->dispatch('notificacao-gerada');
            }
        }
    }

    public function novaVenda(): void
    {
        $this->resetSale();
        $this->passo = 'venda';
        $this->resetErrorBag();
    }

    // ─── Sangria / Suprimento ───

    public function abrirMovModal(string $tipo): void
    {
        $this->movTipo = $tipo;
        $this->movValor = '';
        $this->movMotivo = '';
        $this->movModalOpen = true;
        $this->resetErrorBag('movValor');
    }

    public function fecharMovModal(): void
    {
        $this->movModalOpen = false;
    }

    // ─── Cancelamento ───

    public function abrirListaCancelItem(): void
    {
        $this->cancelItensSelecionados = [];
        $this->cancelMotivo = '';
        $this->cancelModalOpen = false;
        $this->cancelListaOpen = true;
    }

    public function toggleCancelItem(int $idx): void
    {
        if (in_array($idx, $this->cancelItensSelecionados)) {
            $this->cancelItensSelecionados = array_values(array_diff($this->cancelItensSelecionados, [$idx]));
        } else {
            $this->cancelItensSelecionados[] = $idx;
        }
    }

    public function confirmarSelecaoCancela(): void
    {
        if (empty($this->cancelItensSelecionados)) {
            $this->addError('cancelLista', 'Selecione pelo menos um item.');
            return;
        }
        $this->cancelListaOpen = false;
        $this->cancelModalOpen = true;
    }

    public function abrirCancelVenda(): void
    {
        $this->cancelTipo = 'venda';
        $this->cancelMotivo = '';
        $this->cancelModalOpen = true;
    }

    public function confirmarCancelamento(): void
    {
        if (empty(trim($this->cancelMotivo ?? ''))) {
            $this->addError('cancelMotivo', 'Informe o motivo do cancelamento.');
            return;
        }

        if ($this->cancelTipo === 'item') {
            $indices = $this->cancelItensSelecionados;
            rsort($indices);
            foreach ($indices as $i) {
                if (isset($this->carrinho[$i])) {
                    unset($this->carrinho[$i]);
                }
            }
            $this->carrinho = array_values($this->carrinho);
            $qtd = count($indices);
        } else {
            $this->carrinho = [];
            $this->resetErrorBag();
        }

        $this->cancelModalOpen = false;
        $label = $this->cancelTipo === 'item' ? $qtd . ' item(ns) cancelado(s)' : 'Carrinho limpo';
        $this->toast($label . '. Motivo: ' . $this->cancelMotivo);
        $this->cancelItensSelecionados = [];
    }

    // ─── Estorno ───

    public function abrirEstorno(): void
    {
        $this->estornoMotivo = '';
        $this->estornoModalOpen = true;
    }

    // ─── Devolução ───

    public function abrirDevolucao(): void
    {
        $this->devModalOpen = true;
        $this->devBuscaVendaId = '';
        $this->devVenda = null;
        $this->devItensSelecionados = [];
        $this->devMotivo = '';
        $this->devMensagem = '';
    }

    public function buscarVendaDevolucao(): void
    {
        $this->devVenda = null;
        $this->devItensSelecionados = [];
        $this->devMensagem = '';

        $id = (int) $this->devBuscaVendaId;
        if (!$id) { $this->devMensagem = 'Informe o número da venda.'; return; }

        $venda = PdvVenda::with(['itens.variacao', 'pagamentos'])->where('id', $id)->where('status', 'concluida')->first();
        if (!$venda) { $this->devMensagem = 'Venda não encontrada ou não pode ser devolvida.'; return; }

        $this->devVenda = $venda->toArray();
    }

    public function alternarItemDevolucao(int $itemId): void
    {
        if (in_array($itemId, $this->devItensSelecionados)) {
            $this->devItensSelecionados = array_values(array_diff($this->devItensSelecionados, [$itemId]));
        } else {
            $this->devItensSelecionados[] = $itemId;
        }
    }

    public function confirmarDevolucao(): void
    {
        if (!$this->devVenda || empty($this->devItensSelecionados)) {
            $this->devMensagem = 'Selecione pelo menos um item para devolver.';
            return;
        }
        if (empty(trim($this->devMotivo ?? ''))) {
            $this->devMensagem = 'Informe o motivo da devolução.';
            return;
        }

        $venda = PdvVenda::with('itens')->find($this->devVenda['id']);
        if (!$venda || $venda->status !== 'concluida') {
            $this->devMensagem = 'Venda não encontrada.';
            return;
        }

        DB::transaction(function () use ($venda) {
            $venda = PdvVenda::with('itens')->lockForUpdate()->find($venda->id);
            if (!$venda || $venda->status !== 'concluida') {
                $this->devMensagem = 'Venda não disponível.';
                return;
            }

            $totalDevolvido = 0;
            $qtdTotalItens = $venda->itens->count();
            $qtdDevolvidos = 0;

            $dev = PdvDevolucao::create([
                'venda_id' => $venda->id,
                'usuario_id' => auth()->id(),
                'motivo' => $this->devMotivo,
                'valor_total' => 0,
            ]);

            foreach ($venda->itens as $item) {
                if (in_array($item->id, $this->devItensSelecionados)) {
                    $qtd = (float) $item->quantidade;
                    $valUnit = (float) $item->preco_unitario;
                    $descItem = (float) $item->desconto;
                    $totalItem = (float) $item->total_item;
                    $totalDevolvido += $totalItem;
                    $qtdDevolvidos++;

                    PdvDevolucaoItem::create([
                        'devolucao_id' => $dev->id,
                        'produto_variacao_id' => $item->produto_variacao_id,
                        'quantidade' => $qtd,
                        'valor_unitario' => $valUnit,
                    ]);

                    $item->update(['cancelado' => true]);

                    \App\Models\EstoqueSaldo::where('loja_id', (int)$venda->loja_id)
                        ->where('produto_variacao_id', $item->produto_variacao_id)
                        ->increment('quantidade_atual', $qtd);

                    \App\Models\EstoqueMovimentacao::create([
                        'loja_id' => (int)$venda->loja_id,
                        'produto_variacao_id' => $item->produto_variacao_id,
                        'origem_tipo' => 'pdv_devolucao',
                        'origem_id' => $dev->id,
                        'tipo' => 'entrada_devolucao',
                        'quantidade' => $qtd,
                        'justificativa' => 'Devolução venda #' . $venda->id . ': ' . $this->devMotivo,
                        'usuario_id' => auth()->id(),
                    ]);
                }
            }

            $dev->update(['valor_total' => $totalDevolvido]);

            \App\Models\FinanceiroLancamento::create([
                'loja_id' => (int)$venda->loja_id,
                'tipo' => 'receita',
                'descricao' => 'Devolução venda #' . $venda->id,
                'valor' => -$totalDevolvido,
                'data_competencia' => now(),
                'data_vencimento' => now(),
                'data_pagamento' => now(),
                'status' => 'pago',
                'pdv_venda_id' => $venda->id,
            ]);

            if ($qtdDevolvidos >= $qtdTotalItens) {
                \App\Models\PdvVendaPagamento::where('venda_id', $venda->id)
                    ->whereNull('cancelado_at')
                    ->update(['cancelado_at' => now()]);
            }
        });

        $this->devMensagem = 'Devolução registrada com sucesso! Valor: R$ ' . number_format(collect($this->devVenda['itens'])->whereIn('id', $this->devItensSelecionados)->sum(fn($i) => (float)$i['total_item']), 2, ',', '.');
        $this->devVenda = null;
        $this->devItensSelecionados = [];
        $this->devMotivo = '';
        $this->toast('Devolução concluída!');
    }

    public function confirmarEstorno(): void
    {
        if (!$this->vendaId) { $this->addError('estornoMotivo', 'Venda não encontrada.'); return; }
        if (empty(trim($this->estornoMotivo ?? ''))) {
            $this->addError('estornoMotivo', 'Informe o motivo do estorno.'); return;
        }

        $venda = PdvVenda::with('itens')->lockForUpdate()->find($this->vendaId);
        if (!$venda || $venda->status !== 'concluida') {
            $this->addError('estornoMotivo', 'Venda não pode ser estornada.');
            return;
        }

        DB::transaction(function () use ($venda) {
            $venda->update(['status' => 'cancelada']);

            foreach ($venda->itens as $item) {
                if (!$item->cancelado) {
                    $qtd = (float) $item->quantidade;

                    $item->update(['cancelado' => true]);

                    \App\Models\EstoqueSaldo::where('loja_id', (int)$this->loja_id)
                        ->where('produto_variacao_id', $item->produto_variacao_id)
                        ->increment('quantidade_atual', $qtd);

                    \App\Models\EstoqueMovimentacao::create([
                        'loja_id' => (int)$this->loja_id,
                        'produto_variacao_id' => $item->produto_variacao_id,
                        'origem_tipo' => 'pdv_venda',
                        'origem_id' => $venda->id,
                        'tipo' => 'entrada_estorno',
                        'quantidade' => $qtd,
                        'justificativa' => 'Estorno venda #' . $venda->id . ': ' . $this->estornoMotivo,
                        'usuario_id' => auth()->id(),
                    ]);
                }
            }

            \App\Models\PdvVendaPagamento::where('venda_id', $venda->id)
                ->whereNull('cancelado_at')
                ->update(['cancelado_at' => now()]);

            \App\Models\FinanceiroLancamento::where('pdv_venda_id', $venda->id)
                ->update(['status' => 'cancelado']);
        });

        $this->estornoModalOpen = false;
        $this->toast('Venda #' . $this->vendaId . ' estornada com sucesso!');
        $this->novaVenda();
    }

    public function registrarMovimento(): void
    {
        $this->validate([
            'movValor' => ['required', 'regex:/^[0-9]+([.,][0-9]{1,2})?$/'],
            'movMotivo' => ['required', 'string', 'max:255'],
        ]);

        if (!$this->caixaAberturaId) {
            $this->addError('movValor', 'Caixa não está aberto.');
            return;
        }

        if (!$this->verificarAutorizacaoCaixa()) {
            $this->addError('movValor', 'Caixa não pertence ao usuário atual.');
            return;
        }

        $valor = (float) str_replace(',', '.', str_replace('.', '', $this->movValor));

        PdvCaixaMovimento::create([
            'caixa_abertura_id' => $this->caixaAberturaId,
            'usuario_id' => auth()->id(),
            'tipo' => $this->movTipo,
            'valor' => $valor,
            'motivo' => $this->movMotivo,
        ]);

        $label = $this->movTipo === 'sangria' ? 'Sangria' : 'Suprimento';
        $this->toast("{$label} de R$ " . number_format($valor, 2, ',', '.') . ' registrada.');
        $this->fecharMovModal();
    }

    #[Computed]
    public function movimentosCaixa(): array
    {
        if (!$this->caixaAberturaId) return [];
        return PdvCaixaMovimento::where('caixa_abertura_id', $this->caixaAberturaId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function totalSangrias(): float
    {
        if (!$this->caixaAberturaId) return 0;
        return (float) PdvCaixaMovimento::where('caixa_abertura_id', $this->caixaAberturaId)
            ->where('tipo', 'sangria')->sum('valor');
    }

    #[Computed]
    public function totalSuprimentos(): float
    {
        if (!$this->caixaAberturaId) return 0;
        return (float) PdvCaixaMovimento::where('caixa_abertura_id', $this->caixaAberturaId)
            ->where('tipo', 'suprimento')->sum('valor');
    }

    #[Computed]
    public function clientes(): array
    {
        $q = trim($this->buscaCliente);
        if (strlen($q) < 1) return [];
        return Cliente::where('nome', 'like', "%{$q}%")
            ->orWhere('cpf', 'like', "%{$q}%")
            ->orderBy('nome')->limit(10)
            ->get(['id', 'nome', 'cpf'])->toArray();
    }

    #[Computed]
    public function formasPagamento(): array
    {
        return FormaPagamento::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'tipo'])->toArray();
    }

    #[Computed]
    public function formaPagamentoSelecionada(): ?array
    {
        if (!$this->forma_pagamento_id) return null;
        $fp = FormaPagamento::find((int)$this->forma_pagamento_id);
        return $fp?->toArray();
    }

    #[Computed]
    public function subtotal(): float
    {
        $total = 0;
        foreach ($this->carrinho as $item) {
            $qtd = (float) ($item['quantidade'] ?? 0);
            $total += ($item['preco'] ?? 0) * max(0, $qtd);
        }
        return $total;
    }

    #[Computed]
    public function total(): float
    {
        $sub = $this->subtotal;
        $desc = $this->decimal($this->desconto);
        $acr = $this->decimal($this->acrescimo);
        return max(0, $sub - $desc + $acr);
    }

    #[Computed]
    public function trocoCalculado(): float
    {
        $totalDinheiro = collect($this->pagamentos)
            ->where('tipo', 'dinheiro')
            ->sum('valor');
        $total = $this->total;
        return max(0, $totalDinheiro - $total);
    }

    #[Computed]
    public function resultadosProduto(): array
    {
        if (!$this->loja_id || mb_strlen(trim($this->buscaProduto)) < 2) return [];
        $q = $this->buscaProduto;
        return $this->productsForCurrentStore()
            ->where(function ($qry) use ($q): void {
                $qry->whereRaw('MATCH(produto_variacoes.nome_completo) AGAINST(? IN BOOLEAN MODE)', [$q . '*'])
                    ->orWhere('produto_variacoes.nome_completo', 'like', "%{$q}%")
                    ->orWhere('produto_variacoes.sku', 'like', "%{$q}%")
                    ->orWhereIn('produto_variacoes.id', function ($sub) use ($q): void {
                        $sub->select('produto_variacao_id')
                            ->from('produto_codigos_barras')
                            ->where('codigo', 'like', "%{$q}%");
                    });
            })
            ->limit(15)
            ->get()
            ->toArray();
    }

    #[Computed]
    public function lojas(): array
    {
        $q = Loja::where('ativo', true);
        if (auth()->user()->loja_id) {
            $q->where('id', auth()->user()->loja_id);
        }
        return $q->orderBy('nome')->get(['id', 'nome', 'tabela_preco_id'])->toArray();
    }

    #[Computed]
    public function historico(): array
    {
        return PdvVenda::with('pagamentos')
            ->where('caixa_abertura_id', $this->caixaAberturaId)
            ->where('created_at', '>=', now()->startOfDay())
            ->when(mb_strlen(trim($this->filtroHistorico)) >= 1, fn ($query) => $query->where('id', 'like', '%'.$this->filtroHistorico.'%'))
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.venda-manager')
            ->layout('components.layouts.app', ['title' => 'PDV · ERP Mercado', 'noSidebar' => true]);
    }

    private function productsForCurrentStore(): Builder
    {
        $storeId = (int) $this->loja_id;
        $loja = Loja::find($storeId);
        $tableId = $loja?->tabela_preco_id;

        if (!$tableId) {
            return ProdutoVariacao::query()->whereRaw('1=0');
        }

        return ProdutoVariacao::query()
            ->with(['marca:id,nome', 'unidadeMedida:id,sigla'])
            ->join('tabela_precos_itens as price', function ($join) use ($tableId): void {
                $join->on('price.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('price.tabela_preco_id', '=', $tableId);
            })
            ->leftJoin('estoque_saldos as stock', function ($join) use ($storeId): void {
                $join->on('stock.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('stock.loja_id', '=', $storeId);
            })
            ->leftJoin('produto_imagens as img', function ($join): void {
                $join->on('img.produto_variacao_id', '=', 'produto_variacoes.id')
                    ->where('img.principal', true);
            })
            ->where('produto_variacoes.ativo', true)
            ->where('price.preco_venda', '>', 0)
            ->select([
                'produto_variacoes.*',
                'price.preco_venda',
                DB::raw('COALESCE(stock.quantidade_atual - stock.quantidade_reservada, 0) as estoque_disponivel'),
                DB::raw('COALESCE(img.url, null) as foto_url'),
            ]);
    }

    private function resetSale(): void
    {
        $this->vendaId = null;
        $this->carrinho = [];
        $this->cliente_id = '';
        $this->clienteNome = '';
        $this->buscaCliente = '';
        $this->buscaProduto = '';
        $this->codigoBarrasLido = '';
        $this->desconto = '0,00';
        $this->acrescimo = '0,00';
        $this->forma_pagamento_id = '';
        $this->valorRecebido = '0,00';
        $this->troco = '0,00';
        $this->totalFinalizado = 0;
        $this->nfceStatus = null;
        $this->nfceDocumentoId = null;
        $this->filtroHistorico = '';
    }

    public function aplicarDescontoPercentual(): void
    {
        $sub = $this->subtotal;
        $pDesc = $this->decimal($this->descontoPct);
        $pAcr = $this->decimal($this->acrescimoPct);
        if ($pDesc > 0) {
            $this->desconto = number_format($sub * $pDesc / 100, 2, ',', '');
        }
        if ($pAcr > 0) {
            $this->acrescimo = number_format($sub * $pAcr / 100, 2, ',', '');
        }
    }

    private function verificarAutorizacaoCaixa(): bool
    {
        if (!$this->caixaAberturaId) return false;
        $abertura = PdvCaixaAbertura::find($this->caixaAberturaId);
        return $abertura && $abertura->usuario_id === auth()->id();
    }

    private function decimal(mixed $value, int $scale = 2): float
    {
        try {
            return (float) BrazilianNumber::decimal($value, $scale);
        } catch (InvalidArgumentException) {
            return 0;
        }
    }
}
