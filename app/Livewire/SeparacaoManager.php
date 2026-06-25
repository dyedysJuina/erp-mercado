<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PedidoSeparacao;
use App\Models\PedidoSeparacaoItem;
use App\Models\PedidoStatusHistorico;
use App\Models\EstoqueSaldo;
use App\Models\ProdutoVariacao;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class SeparacaoManager extends Component
{
    public int $pedidoId;
    public int $itemAtual = 0;
    public array $itens = [];
    public array $processados = [];
    public array $cancelados = [];
    public string $toastMsg = '';
    public bool $toastShow = false;
    public ?int $separacaoId = null;
    public string $bloqueioErro = '';
    public string $scanFeedback = '';
    public string $buscaSubstituto = '';
    public array $resultadosSubstituto = [];
    public bool $showSubstituto = false;
    public int $volumes = 1;
    public bool $conferenciaAprovada = false;

    public function mount(int $id): void
    {
        $this->pedidoId = $id;
        $this->iniciarOuRetomarSeparacao();
    }

    public function iniciarOuRetomarSeparacao(): void
    {
        try {
            $existente = PedidoSeparacao::where('pedido_id', $this->pedidoId)
                ->whereIn('status', ['em_andamento', 'pausada'])
                ->first();

            if ($existente) {
                if ($existente->separador_id !== auth()->id()) {
                    $separador = $existente->separador?->name ?? 'outro usuário';
                    $this->bloqueioErro = "Este pedido já está em separação por {$separador}.";
                    return;
                }
                $this->separacaoId = $existente->id;
                $existente->update(['status' => 'em_andamento']);
            } else {
                $sep = PedidoSeparacao::create([
                    'pedido_id' => $this->pedidoId,
                    'separador_id' => auth()->id(),
                    'status' => 'em_andamento',
                    'inicio_at' => now(),
                ]);
                $this->separacaoId = $sep->id;
            }
        } catch (\Exception $e) {
            // Tabela pode não existir (migration pendente) — segue sem bloqueio
            $this->separacaoId = null;
        }

        $this->carregarItens();
    }

    public function carregarItens(): void
    {
        $pedido = Pedido::with('itens.variacao.produtoBase.categoria')->findOrFail($this->pedidoId);
        $todos = $pedido->itens->values();

        $variacaoIds = $todos->pluck('produto_variacao_id')->filter()->unique()->toArray();

        // Batch query localizacao via EstoqueSaldo -> EstoqueLocal
        $locais = collect();
        if (!empty($variacaoIds) && $pedido->loja_id) {
            $saldos = EstoqueSaldo::with('local')
                ->whereIn('produto_variacao_id', $variacaoIds)
                ->where('loja_id', $pedido->loja_id)
                ->where('quantidade_atual', '>', 0)
                ->get()
                ->groupBy('produto_variacao_id');
            $locais = $saldos->map(fn($g) => $g->first()?->local);
        }

        $this->cancelados = $todos->filter(fn($i) => $i->status_item === 'cancelado')
            ->map(fn($i) => $this->mapearItem($i, $locais))->toArray();

        $restantes = $todos->filter(fn($i) => $i->status_item !== 'cancelado');
        $jaProcessados = $restantes->filter(fn($i) => in_array($i->status_item, ['separado', 'faltou', 'substituido', 'quantidade_alterada']));
        $pendentes = $restantes->filter(fn($i) => $i->status_item === 'pendente');

        $this->processados = $jaProcessados->map(fn($i) => $this->mapearItem($i, $locais))->toArray();
        $this->itens = $pendentes->map(fn($i) => $this->mapearItem($i, $locais))->toArray();

        // Sort pendentes by categoria for grouping
        usort($this->itens, fn($a, $b) => strcmp($a['categoria'] ?? '', $b['categoria'] ?? ''));

        // Garantir PedidoSeparacaoItem para itens pendentes
        if ($this->separacaoId) {
            foreach ($this->itens as $item) {
                PedidoSeparacaoItem::firstOrCreate([
                    'separacao_id' => $this->separacaoId,
                    'pedido_item_id' => $item['id'],
                ], ['quantidade_separada' => 0, 'status' => 'pendente']);
            }
        }

        $this->itemAtual = 0;
    }

    private function mapearItem($i, $locais = null): array
    {
        $cat = $i->variacao?->produtoBase?->categoria;
        $local = $locais ? $locais->get($i->produto_variacao_id) : null;
        return [
            'id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'qtd_pedido' => (float)$i->quantidade_solicitada,
            'qtd_separada' => (float)($i->quantidade_separada ?: 0),
            'status' => $i->status_item,
            'observacao' => $i->observacao_separacao ?? '',
            'sku' => $i->variacao?->sku ?? '',
            'foto' => $i->variacao?->foto_capa_url ?? '',
            'localizacao' => $local ? trim(implode(' > ', array_filter([$local->corredor, $local->prateleira]))) : null,
            'categoria' => $cat?->caminho ?? ($cat?->nome ?? 'Geral'),
            'substituto_id' => (int)($i->substituto_produto_variacao_id ?: 0),
            'substituto_nome' => $i->substituto?->nome_completo ?? '',
        ];
    }

    public function getItemProperty(): ?array
    {
        return $this->itens[$this->itemAtual] ?? null;
    }

    public function pedido(): ?Pedido
    {
        return Pedido::with('cliente')->find($this->pedidoId);
    }

    public function progresso(): array
    {
        $total = count($this->itens) + count($this->processados);
        $feitos = count($this->processados);
        $separados = count(array_filter($this->processados, fn($i) => $i['status'] === 'separado'));
        $faltou = count(array_filter($this->processados, fn($i) => $i['status'] === 'faltou'));
        $substituidos = count(array_filter($this->processados, fn($i) => $i['status'] === 'substituido'));
        $altQtd = count(array_filter($this->processados, fn($i) => $i['status'] === 'quantidade_alterada'));

        return [
            'total' => $total, 'feitos' => $feitos,
            'pct' => $total > 0 ? round(($feitos / $total) * 100) : 0,
            'separados' => $separados, 'faltou' => $faltou,
            'substituidos' => $substituidos, 'altQtd' => $altQtd,
            'cancelados' => count($this->cancelados),
            'pctOk' => $total > 0 ? round(($separados / $total) * 100) : 0,
            'pctFaltou' => $total > 0 ? round(($faltou / $total) * 100) : 0,
            'pctSubst' => $total > 0 ? round(($substituidos / $total) * 100) : 0,
        ];
    }

    public function definirQuantidade(float $qtd): void
    {
        if (isset($this->itens[$this->itemAtual])) {
            $this->itens[$this->itemAtual]['qtd_separada'] = max(0, min($this->itens[$this->itemAtual]['qtd_pedido'], $qtd));
        }
    }

    public function incrementar(): void
    {
        $this->definirQuantidade(($this->itens[$this->itemAtual]['qtd_separada'] ?? 0) + 1);
    }

    public function decrementar(): void
    {
        $this->definirQuantidade(($this->itens[$this->itemAtual]['qtd_separada'] ?? 0) - 1);
    }

    public function definirStatus(string $status): void
    {
        if (!isset($this->itens[$this->itemAtual])) return;
        $item = &$this->itens[$this->itemAtual];

        if ($status === 'ok') {
            $item['qtd_separada'] = $item['qtd_pedido'];
            $item['status'] = 'separado';
            $item['observacao'] = 'OK';
        } elseif ($status === 'parcial') {
            $item['qtd_separada'] = max(1, $item['qtd_pedido'] - 1);
            $item['status'] = 'separado';
            $item['observacao'] = 'Falta ' . ($item['qtd_pedido'] - $item['qtd_separada']) . ' un';
        } elseif ($status === 'faltou') {
            $item['qtd_separada'] = 0;
            $item['status'] = 'faltou';
            $item['observacao'] = 'Sem estoque na gôndola';
        } elseif ($status === 'qtd_alterada') {
            $item['status'] = 'quantidade_alterada';
            $item['observacao'] = $item['observacao'] ?: 'Quantidade ajustada manualmente';
        }
    }

    public function buscarPorCodigoBarras(string $codigo): void
    {
        $this->scanFeedback = '';

        $barcode = DB::table('produto_codigos_barras')
            ->where('codigo', $codigo)
            ->first();

        if (!$barcode) {
            $this->scanFeedback = 'nao_encontrado';
            $this->toast('Código de barras não encontrado.');
            return;
        }

        foreach ($this->itens as $idx => $item) {
            if ((int)$item['variacao_id'] === (int)$barcode->produto_variacao_id) {
                if ($idx === $this->itemAtual) {
                    $this->scanFeedback = 'ok';
                    $this->toast('Item atual confirmado por código de barras!');
                    // Auto-confirmar
                    $this->definirStatus('ok');
                    $this->confirmarProximo();
                } else {
                    $this->itemAtual = $idx;
                    $this->scanFeedback = 'ok';
                    $this->toast('Navegando para item: ' . $item['nome']);
                }
                return;
            }
        }

        foreach ($this->processados as $pr) {
            if ((int)$pr['variacao_id'] === (int)$barcode->produto_variacao_id) {
                $this->scanFeedback = 'ja_processado';
                $this->toast('Este item já foi processado.');
                return;
            }
        }

        $this->scanFeedback = 'nao_encontrado';
        $this->toast('Item não encontrado neste pedido.');
    }

    public function abrirSubstituto(): void
    {
        $this->buscaSubstituto = '';
        $this->resultadosSubstituto = [];
        $this->showSubstituto = true;
    }

    public function fecharSubstituto(): void
    {
        $this->showSubstituto = false;
        $this->resultadosSubstituto = [];
    }

    public function buscarSubstituto(): void
    {
        $q = trim($this->buscaSubstituto);
        if (strlen($q) < 2) { $this->resultadosSubstituto = []; return; }

        $this->resultadosSubstituto = ProdutoVariacao::with('produtoBase')
            ->where('ativo', true)
            ->where('nome_completo', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(fn($v) => [
                'id' => $v->id,
                'nome' => $v->nome_completo,
                'sku' => $v->sku,
                'preco' => $v->precosTabela->first()?->preco_venda ?? 0,
            ])
            ->toArray();
    }

    public function selecionarSubstituto(int $variacaoId): void
    {
        if (!isset($this->itens[$this->itemAtual])) return;
        $item = &$this->itens[$this->itemAtual];

        // Find the substitute name
        $sub = collect($this->resultadosSubstituto)->firstWhere('id', $variacaoId);
        $nomeSub = $sub['nome'] ?? "#{$variacaoId}";

        $item['substituto_id'] = $variacaoId;
        $item['substituto_nome'] = $nomeSub;
        $item['status'] = 'substituido';
        $item['observacao'] = "Substituído por: {$nomeSub}";
        $this->showSubstituto = false;
        $this->toast("Substituto selecionado: {$nomeSub}");
    }

    public function msgWhatsApp(string $tipo): string
    {
        $pedido = $this->pedido();
        $cliente = $pedido?->cliente;
        if (!$cliente || !$cliente->whatsapp) return '';

        $numero = preg_replace('/\D/', '', $cliente->whatsapp);
        $mensagens = [
            'iniciando' => "Olá {$cliente->nome}! Seu pedido #{$pedido->id} está sendo separado. Em breve avisamos quando estiver pronto. 🛒",
            'faltou' => "Oi {$cliente->nome}! Infelizmente alguns itens do seu pedido #{$pedido->id} estão em falta. Vamos te atualizar em breve.",
            'pronto' => "{$cliente->nome}, seu pedido #{$pedido->id} já está separado e pronto para retirada/entrega! ✅",
            'substituicao' => "{$cliente->nome}, precisamos autorizar uma substituição no pedido #{$pedido->id}. Entraremos em contato.",
        ];

        $texto = $mensagens[$tipo] ?? $mensagens['pronto'];
        return "https://wa.me/55{$numero}?text=" . urlencode($texto);
    }

    public function confirmarProximo(): void
    {
        $item = $this->itens[$this->itemAtual] ?? null;
        if (!$item) return;

        $statusFinal = $item['status'];
        if ($statusFinal === 'pendente') {
            if ($item['qtd_separada'] >= $item['qtd_pedido']) {
                $statusFinal = 'separado';
                $item['observacao'] = 'OK';
            } elseif ($item['qtd_separada'] > 0) {
                $statusFinal = 'quantidade_alterada';
                $item['observacao'] = $item['observacao'] ?: 'Quantidade ajustada manualmente';
            } else {
                $statusFinal = 'faltou';
                $item['observacao'] = $item['observacao'] ?: 'Não separado';
            }
        }

        DB::transaction(function () use ($item, $statusFinal) {
            $data = [
                'status_item' => $statusFinal,
                'quantidade_separada' => $item['qtd_separada'],
                'observacao_separacao' => $item['observacao'] ?: null,
                'separado_por' => auth()->id(),
            ];
            if (!empty($item['substituto_id'])) {
                $data['substituto_produto_variacao_id'] = $item['substituto_id'];
            }
            PedidoItem::where('id', $item['id'])->update($data);

            if ($this->separacaoId) {
                PedidoSeparacaoItem::updateOrCreate(
                    ['separacao_id' => $this->separacaoId, 'pedido_item_id' => $item['id']],
                    ['quantidade_separada' => $item['qtd_separada'], 'status' => $statusFinal, 'observacao' => $item['observacao']]
                );
            }
        });

        $item['status'] = $statusFinal;
        $this->processados[] = $item;
        array_splice($this->itens, $this->itemAtual, 1);

        if (count($this->itens) === 0) {
            $pedido = Pedido::find($this->pedidoId);
            if ($pedido && $pedido->status === 'recebido') {
                $pedido->update(['status' => 'em_separacao']);
                $this->logStatusHistorico($pedido, 'recebido', 'em_separacao', 'Separação iniciada');
            }
        }
    }

    public function pularItem(): void
    {
        $this->itemAtual++;
    }

    public function abrirConferencia(): void
    {
        $this->conferenciaAprovada = false;
    }

    public function finalizarSeparacao()
    {
        if (!$this->conferenciaAprovada) return;

        DB::transaction(function () {
            for ($i = 0; $i < count($this->itens); $i++) {
                $item = $this->itens[$i];
                $st = $item['status'] ?: ($item['qtd_separada'] > 0 ? 'separado' : 'faltou');
                PedidoItem::where('id', $item['id'])->update([
                    'status_item' => $st,
                    'quantidade_separada' => $item['qtd_separada'],
                    'observacao_separacao' => $item['observacao'] ?: null,
                ]);
                if ($this->separacaoId) {
                    PedidoSeparacaoItem::updateOrCreate(
                        ['separacao_id' => $this->separacaoId, 'pedido_item_id' => $item['id']],
                        ['quantidade_separada' => $item['qtd_separada'], 'status' => $st, 'observacao' => $item['observacao']]
                    );
                }
            }

            $pedido = Pedido::find($this->pedidoId);
            if ($pedido) {
                $pendentes = PedidoItem::where('pedido_id', $pedido->id)
                    ->whereIn('status_item', ['pendente', 'faltou', 'cancelado'])->count();
                $novoStatus = $pendentes === 0 ? 'pronto_retirada' : 'em_separacao';
                $this->logStatusHistorico($pedido, $pedido->status, $novoStatus, 'Separação finalizada');
                $pedido->update(['status' => $novoStatus]);
            }

            if ($this->separacaoId) {
                PedidoSeparacao::where('id', $this->separacaoId)->update([
                    'status' => 'finalizada', 'fim_at' => now(),
                ]);
            }
        });

        $this->toast('Separação finalizada!');
        return $this->redirect('/separacao');
    }

    private function logStatusHistorico($pedido, $antigo, $novo, $obs = null): void
    {
        PedidoStatusHistorico::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => auth()->id(),
            'status_anterior' => $antigo,
            'status_novo' => $novo,
            'observacao' => $obs,
        ]);
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.separacao-manager')
            ->layout('components.layouts.app', ['title' => 'Separando Pedido #' . $this->pedidoId . ' · ERP Mercado', 'noSidebar' => true]);
    }
}
