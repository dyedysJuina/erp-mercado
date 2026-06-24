<?php

namespace App\Livewire;

use App\Models\Notificacao;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class NotificacaoManager extends Component
{
    use WithPagination;

    public string $aba = 'pendentes';
    public string $busca = '';
    public string $filtroTipo = '';
    public string $dataInicio = '';
    public string $dataFim = '';

    public string $toastMsg = '';
    public bool $toastShow = false;

    public function mount(): void
    {
        $this->verificarAlertas();
    }

    public function pendentes()
    {
        $q = Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->orderBy('created_at', 'desc');

        if ($this->filtroTipo) {
            $q->where('titulo', $this->filtroTipo);
        }
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('mensagem', 'like', '%' . $this->busca . '%');
        }
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);

        return $q->paginate(20);
    }

    public function todas()
    {
        $q = Notificacao::where('usuario_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($this->filtroTipo) {
            $q->where('titulo', $this->filtroTipo);
        }
        if (strlen(trim($this->busca)) >= 2) {
            $q->where('mensagem', 'like', '%' . $this->busca . '%');
        }
        if ($this->dataInicio) $q->whereDate('created_at', '>=', $this->dataInicio);
        if ($this->dataFim) $q->whereDate('created_at', '<=', $this->dataFim);

        return $q->paginate(20);
    }

    #[Computed]
    public function totalNaoLidas(): int
    {
        return Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->count();
    }

    #[Computed]
    public function totais(): array
    {
        return [
            'pendentes' => Notificacao::where('usuario_id', auth()->id())->whereIn('status', ['pendente', 'enviada'])->count(),
            'lidas' => Notificacao::where('usuario_id', auth()->id())->where('status', 'lida')->count(),
        ];
    }

    #[Computed]
    public function tiposFiltro(): array
    {
        return Notificacao::where('usuario_id', auth()->id())
            ->select('titulo')
            ->distinct()
            ->orderBy('titulo')
            ->pluck('titulo')
            ->toArray();
    }

    public function marcarLida(int $id): void
    {
        Notificacao::where('id', $id)->where('usuario_id', auth()->id())
            ->update(['status' => 'lida', 'lida_at' => now()]);
        $this->dispatch('notificacao-gerada');
    }

    public function marcarTodasLidas(): void
    {
        Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->update(['status' => 'lida', 'lida_at' => now()]);
        $this->dispatch('notificacao-gerada');
        $this->toast('Todas marcadas como lidas.');
    }

    public function excluir(int $id): void
    {
        Notificacao::where('id', $id)->where('usuario_id', auth()->id())->delete();
        $this->dispatch('notificacao-gerada');
        $this->toast('Notificacao excluida.');
    }

    public function limparLidas(): void
    {
        Notificacao::where('usuario_id', auth()->id())->where('status', 'lida')->delete();
        $this->toast('Notificacoes lidas excluidas.');
    }

    public function verificarAlertas(): void
    {
        $userId = auth()->id();
        $count = 0;

        $estoqueBaixo = DB::table('estoque_saldos')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_saldos.produto_variacao_id')
            ->where('estoque_saldos.quantidade_atual', '>', 0)
            ->whereColumn('estoque_saldos.quantidade_atual', '<=', 'estoque_saldos.estoque_minimo')
            ->select('produto_variacoes.id as variacao_id', 'produto_variacoes.nome_completo', 'estoque_saldos.quantidade_atual', 'estoque_saldos.estoque_minimo')
            ->get();

        foreach ($estoqueBaixo as $e) {
            $existe = Notificacao::where('usuario_id', $userId)
                ->where('titulo', 'Estoque Baixo')
                ->where('mensagem', 'like', "%{$e->nome_completo}%")
                ->where('created_at', '>=', now()->subDay())
                ->exists();
            if ($existe) continue;

            Notificacao::create([
                'usuario_id' => $userId,
                'canal' => 'sistema',
                'titulo' => 'Estoque Baixo',
                'mensagem' => "{$e->nome_completo} - Estoque atual: {$e->quantidade_atual}, Minimo: {$e->estoque_minimo}",
                'link' => '/estoque',
                'status' => 'pendente',
            ]);
            $count++;
        }

        $lotesVencendo = DB::table('estoque_lotes')
            ->join('produto_variacoes', 'produto_variacoes.id', '=', 'estoque_lotes.produto_variacao_id')
            ->where('estoque_lotes.quantidade_atual', '>', 0)
            ->whereBetween('estoque_lotes.data_validade', [now(), now()->addDays(30)])
            ->select('produto_variacoes.nome_completo', 'estoque_lotes.numero_lote', 'estoque_lotes.data_validade', 'estoque_lotes.quantidade_atual')
            ->get();

        foreach ($lotesVencendo as $l) {
            $existe = Notificacao::where('usuario_id', $userId)
                ->where('titulo', 'Lote Vencendo')
                ->where('mensagem', 'like', "%{$l->nome_completo}%")
                ->where('created_at', '>=', now()->subDay())
                ->exists();
            if ($existe) continue;

            $dias = now()->diffInDays($l->data_validade);
            Notificacao::create([
                'usuario_id' => $userId,
                'canal' => 'sistema',
                'titulo' => 'Lote Vencendo',
                'mensagem' => "{$l->nome_completo} - Lote {$l->numero_lote} vence em {$dias} dias. Quantidade: {$l->quantidade_atual}",
                'link' => '/lotes',
                'status' => 'pendente',
            ]);
            $count++;
        }

        $contas = DB::table('financeiro_lancamentos')
            ->where('tipo', 'despesa')
            ->where('status', 'pendente')
            ->whereBetween('data_vencimento', [now(), now()->addDays(7)])
            ->select('descricao', 'valor', 'data_vencimento')
            ->get();

        foreach ($contas as $c) {
            $existe = Notificacao::where('usuario_id', $userId)
                ->where('titulo', 'Conta a Pagar')
                ->where('mensagem', 'like', "%{$c->descricao}%")
                ->where('created_at', '>=', now()->subDay())
                ->exists();
            if ($existe) continue;

            $dias = now()->diffInDays($c->data_vencimento);
            Notificacao::create([
                'usuario_id' => $userId,
                'canal' => 'sistema',
                'titulo' => 'Conta a Pagar',
                'mensagem' => "{$c->descricao} - Valor: R$ " . number_format($c->valor, 2, ',', '.') . " - Vence em {$dias} dias",
                'link' => '/financeiro',
                'status' => 'pendente',
            ]);
            $count++;
        }

        if ($count > 0) {
            $this->dispatch('notificacao-gerada');
            $this->toast("{$count} novo(s) alerta(s) gerado(s)!");
        }
    }

    public function toast(string $msg): void
    {
        $this->toastMsg = $msg;
        $this->toastShow = true;
    }

    public function render()
    {
        return view('livewire.notificacao-manager')
            ->layout('components.layouts.app', ['title' => 'Notificacoes · ERP Mercado']);
    }
}
