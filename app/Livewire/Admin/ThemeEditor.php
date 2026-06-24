<?php

namespace App\Livewire\Admin;

use App\Models\Theme;
use App\Services\ThemeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeEditor extends Component
{
    public Theme $editing;

    public string $name = '';

    public bool $is_active = true;

    public array $tokens = [];

    public function mount(?int $themeId = null): void
    {
        $theme = $themeId ? Theme::findOrFail($themeId) : Theme::where('slug', 'verde')->firstOrFail();
        $this->editing = $theme;
        $this->name = $theme->name;
        $this->is_active = $theme->is_active;
        $this->tokens = $theme->tokens;
    }

    public function selectTheme(int $id): void
    {
        session()->forget('saved');
        $this->mount($id);
        $this->dispatch('theme-preview', theme: $this->editing->slug, tokens: $this->tokens);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'is_active' => ['boolean'],
            'tokens' => ['required', 'array'],
            'tokens.*' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,8})$/'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'O nome do tema é obrigatório.',
            'tokens.*.required' => 'Todos os tokens de cor são obrigatórios.',
            'tokens.*.regex' => 'Cada token deve ser uma cor hexadecimal válida (#RRGGBB).',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->editing->update([
            'name' => $this->name,
            'is_active' => $this->is_active,
            'tokens' => $this->tokens,
        ]);

        app(ThemeService::class)->clearCache();

        $user = Auth::user();
        if ($user) {
            $user->update(['theme' => $this->editing->slug]);
        }

        $this->dispatch('theme-updated', theme: $this->editing->slug, tokens: $this->tokens);
        session()->flash('saved', true);
    }

    public function render()
    {
        $themes = Theme::orderBy('id')->get();

        return view('livewire.admin.theme-editor', [
            'themes' => $themes,
        ])->layout('components.layouts.app', ['title' => 'Gerenciar temas · ERP Mercado']);
    }
}
