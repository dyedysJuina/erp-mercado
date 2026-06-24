<?php

namespace App\Livewire;

use App\Models\Notificacao;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $count = 0;
    public int $sinal = 0;

    public function mount(): void
    {
        $this->count = $this->contar();
    }

    public function getListeners(): array
    {
        return ['notificacao-gerada' => '$refresh'];
    }

    public function atualizar(): void
    {
        $novoCount = $this->contar();
        if ($novoCount > $this->count) {
            $this->sinal++;
            $this->dispatch('notificacao-nova');
        }
        $this->count = $novoCount;
    }

    private function contar(): int
    {
        return Notificacao::where('usuario_id', auth()->id())
            ->whereIn('status', ['pendente', 'enviada'])
            ->count();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
