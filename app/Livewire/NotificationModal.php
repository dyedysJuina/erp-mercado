<?php

namespace App\Livewire;

use App\Models\Notificacao;
use Livewire\Component;

class NotificationModal extends Component
{
    public int $sinal = 0;
    public bool $modalAberto = false;
    public int $totalNaoLidas = 0;
    public array $naoLidas = [];

    public function getListeners(): array
    {
        return [
            'notificacao-gerada' => 'atualizar',
            'notificacao-nova' => 'atualizar',
        ];
    }

    public function atualizar(): void
    {
        $this->carregar();
        $this->modalAberto = true;
        $this->sinal++;
    }

    private function carregar(): void
    {
        $this->totalNaoLidas = Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->count();

        $this->naoLidas = Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function mount(): void
    {
        $this->carregar();
    }

    public function marcarLida(int $id): void
    {
        Notificacao::where('id', $id)->where('usuario_id', auth()->id())
            ->update(['status' => 'lida', 'lida_at' => now()]);
        $this->carregar();
        $this->dispatch('notificacao-gerada');
    }

    public function marcarTodas(): void
    {
        Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->update(['status' => 'lida', 'lida_at' => now()]);
        $this->carregar();
        $this->dispatch('notificacao-gerada');
    }

    public function fechar(): void
    {
        $this->modalAberto = false;
    }

    public function render()
    {
        return view('livewire.notification-modal');
    }
}
