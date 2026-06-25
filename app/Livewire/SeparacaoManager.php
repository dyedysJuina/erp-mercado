<?php

namespace App\Livewire;

use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\PedidoSeparacao;
use App\Models\PedidoSeparacaoItem;
use App\Models\PedidoStatusHistorico;
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

    public function mount(int $id): void
    {
        $this->pedidoId = $id;
        $this->iniciarOuRetomarSeparacao();
    }

    public function iniciarOuRetomarSeparacao(): void
    {
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

        $this->carregarItens();
    }

    public function carregarItens(): void
    {
        $pedido = Pedido::with('itens.variacao')->findOrFail($this->pedidoId);
        $todos = $pedido->itens->values();

        $this->cancelados = $todos->filter(fn($i) => $i->status_item === 'cancelado')
            ->map(fn($i) => $this->mapearItem($i))->toArray();

        $restantes = $todos->filter(fn($i) => $i->status_item !== 'cancelado');
        $jaProcessados = $restantes->filter(fn($i) => in_array($i->status_item, ['separado', 'faltou', 'substituido', 'quantidade_alterada']));
        $pendentes = $restantes->filter(fn($i) => $i->status_item === 'pendente');

        $this->processados = $jaProcessados->map(fn($i) => $this->mapearItem($i))->toArray();
        $this->itens = $pendentes->map(fn($i) => $this->mapearItem($i))->toArray();

        // Garantir PedidoSeparacaoItem para itens pendentes
        if ($this->separacaoId) {
            foreach ($this->itens as $item) {
                PedidoSeparacaoItem::firstOrCreate([
                    'separacao_id' => $this->separacaoId,
                    'pedido_item_id' => $item['id'],
                ], [
                    'quantidade_separada' => 0,
                    'status' => 'pendente',
                ]);
            }
        }

        $this->itemAtual = 0;
    }

    private function mapearItem($i): array
    {
        return [
            'id' => $i->id,
            'variacao_id' => $i->produto_variacao_id,
            'nome' => $i->variacao?->nome_completo ?? '#' . $i->produto_variacao_id,
            'qtd_pedido' => (float)$i->quantidade_solicitada,
            'qtd_separada' => (float)($i->quantidade_separada ?: 0),
            'status' => $i->status_item,
            'observacao' => $i->observacao_separacao ?? '',
            'sku' => $i->variacao?->sku ?? '',
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

    public function confirmarProximo(): void
    {
        $item = $this->itens[$this->itemAtual] ?? null;
        if (!$item) return;

        // Determinar status automatico se nenhum botao foi pressionado
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
            PedidoItem::where('id', $item['id'])->update([
                'status_item' => $statusFinal,
                'quantidade_separada' => $item['qtd_separada'],
                'observacao_separacao' => $item['observacao'] ?: null,
                'separado_por' => auth()->id(),
            ]);

            if ($this->separacaoId) {
                PedidoSeparacaoItem::updateOrCreate(
                    ['separacao_id' => $this->separacaoId, 'pedido_item_id' => $item['id']],
                    [
                        'quantidade_separada' => $item['qtd_separada'],
                        'status' => $statusFinal,
                        'observacao' => $item['observacao'],
                    ]
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

    public function finalizarSeparacao()
    {
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
                    'status' => 'finalizada',
                    'fim_at' => now(),
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
