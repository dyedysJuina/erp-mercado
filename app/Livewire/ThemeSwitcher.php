<?php

namespace App\Livewire;

use App\Services\ThemeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $current = 'verde';

    public function mount(): void
    {
        $this->current = Auth::user()?->theme ?? 'verde';
    }

    public function setTheme(string $theme): void
    {
        $temas = app(ThemeService::class)->allActive();
        if (! isset($temas[$theme])) {
            return;
        }

        $user = Auth::user();
        if ($user) {
            $user->update(['theme' => $theme]);
            $this->current = $theme;
        }

        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render()
    {
        return view('livewire.theme-switcher', [
            'temas' => app(ThemeService::class)->allActive(),
        ]);
    }
}
