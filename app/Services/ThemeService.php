<?php

namespace App\Services;

use App\Models\Theme;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ThemeService
{
    private function tableExiste(): bool
    {
        return Cache::remember('themes.table-exists', 3600, fn() => Schema::hasTable('themes'));
    }

    public function getAllOverridesCss(): string
    {
        if (! $this->tableExiste()) {
            return '';
        }

        return Cache::remember('themes.generated-css', 3600, function (): string {
            return Theme::query()
                ->where('is_active', true)
                ->orderBy('id')
                ->get()
                ->map(function (Theme $theme): string {
                    if (! preg_match('/^[a-z0-9-]+$/', $theme->slug)) {
                        return '';
                    }

                    $variables = collect($theme->tokens)
                        ->filter(fn ($value, $key) => preg_match('/^[a-z0-9_]+$/', (string) $key)
                            && preg_match('/^#[0-9a-fA-F]{3,8}$/', (string) $value))
                        ->map(fn ($value, $key) => '    --'.str_replace('_', '-', $key).": {$value};")
                        ->implode("\n");

                    return "[data-theme='{$theme->slug}'] {\n{$variables}\n}";
                })
                ->filter()
                ->implode("\n\n");
        });
    }

    public function resolveTokens(string $slug): array
    {
        if (! $this->tableExiste()) return [];
        return Theme::where('slug', $slug)->first()?->tokens ?? [];
    }

    public function allActive(): array
    {
        if (! $this->tableExiste()) {
            return [];
        }

        return Cache::remember('themes.active', 3600, function () {
            return Theme::where('is_active', true)
                ->get(['slug', 'name', 'tokens'])
                ->mapWithKeys(fn (Theme $theme) => [
                    $theme->slug => [
                        'name' => $theme->name,
                        'color' => $theme->tokens['primary_500'] ?? 'transparent',
                    ],
                ])
                ->all();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('themes.generated-css');
        Cache::forget('themes.active');
        Cache::forget('themes.table-exists');
    }
}
