<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    private array $semanticTokens = [
        'on_primary' => '#ffffff',
        'on_success' => '#ffffff',
        'on_warning' => '#1e293b',
        'on_danger' => '#ffffff',
        'shadow' => '#0b0f19',
        'success' => '#10b981',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
    ];

    private array $temas = [
        // ═══════════════════════════════════════════════
        //  VERDE OPERACIONAL (Obsidian & Emerald)
        // ═══════════════════════════════════════════════
        'verde' => [
            'name' => 'Verde Operacional',
            'tokens' => [
                'primary_50' => '#ecfdf5',
                'primary_100' => '#d1fae5',
                'primary_200' => '#a7f3d0',
                'primary_300' => '#6ee7b7',
                'primary_400' => '#34d399',
                'primary_500' => '#10b981',
                'primary_600' => '#059669',
                'primary_700' => '#047857',
                'primary_800' => '#065f46',
                'primary_900' => '#064e3b',

                'surface' => '#ffffff',
                'background' => '#eef1f5',
                'text' => '#111827',
                'muted' => '#4b5563',
                'border' => '#e2e8f0',

                'sidebar_surface' => '#0f172a',
                'sidebar_text' => '#f8fafc',
                'sidebar_muted' => '#94a3b8',
                'sidebar_active_bg' => '#1e293b',
                'sidebar_active_text' => '#ffffff',
                'sidebar_active_dot' => '#38bdf8',

                'header_bg' => '#ffffff',
                'header_border' => '#e2e8f0',
            ],
        ],

        // ═══════════════════════════════════════════════
        //  AZUL PROFISSIONAL (Obsidian & Electric Blue/Indigo)
        // ═══════════════════════════════════════════════
        'azul' => [
            'name' => 'Azul Profissional',
            'tokens' => [
                'primary_50' => '#eef2ff',
                'primary_100' => '#e0e7ff',
                'primary_200' => '#c7d2fe',
                'primary_300' => '#a5b4fc',
                'primary_400' => '#818cf8',
                'primary_500' => '#4f46e5',
                'primary_600' => '#4338ca',
                'primary_700' => '#3730a3',
                'primary_800' => '#312e81',
                'primary_900' => '#1e1b4b',

                'surface' => '#ffffff',
                'background' => '#f9fafb',
                'text' => '#111827',
                'muted' => '#6b7280',
                'border' => '#f3f4f6',

                'sidebar_surface' => '#080b11',
                'sidebar_text' => '#f3f4f6',
                'sidebar_muted' => '#9ca3af',
                'sidebar_active_bg' => '#4f46e5',
                'sidebar_active_text' => '#ffffff',
                'sidebar_active_dot' => '#818cf8',

                'header_bg' => '#ffffff',
                'header_border' => '#f3f4f6',
            ],
        ],

        // ═══════════════════════════════════════════════
        //  AMBAR MERCADO
        // ═══════════════════════════════════════════════
        'ambar' => [
            'name' => 'Âmbar Mercado',
            'tokens' => [
                'primary_50' => '#fffbeb',
                'primary_100' => '#fef3c7',
                'primary_200' => '#fde68a',
                'primary_300' => '#fcd34d',
                'primary_400' => '#fbbf24',
                'primary_500' => '#f59e0b',
                'primary_600' => '#d97706',
                'primary_700' => '#b45309',
                'primary_800' => '#92400e',
                'primary_900' => '#78350f',

                'surface' => '#ffffff',
                'background' => '#f0efed',
                'text' => '#1c1917',
                'muted' => '#57534e',
                'border' => '#d6d3d1',

                'sidebar_surface' => '#141210',
                'sidebar_text' => '#f5f5f4',
                'sidebar_muted' => '#a8a29e',
                'sidebar_active_bg' => '#f59e0b',
                'sidebar_active_text' => '#1c1917',
                'sidebar_active_dot' => '#78350f',

                'header_bg' => '#ffffff',
                'header_border' => '#d6d3d1',
            ],
        ],

        // ═══════════════════════════════════════════════
        //  VIOLETA CRIATIVO
        // ═══════════════════════════════════════════════
        'violeta' => [
            'name' => 'Violeta Criativo',
            'tokens' => [
                'primary_50' => '#faf5ff',
                'primary_100' => '#f3e8ff',
                'primary_200' => '#e9d5ff',
                'primary_300' => '#d8b4fe',
                'primary_400' => '#c084fc',
                'primary_500' => '#8b5cf6',
                'primary_600' => '#7c3aed',
                'primary_700' => '#6d28d9',
                'primary_800' => '#5b21b6',
                'primary_900' => '#4c1d95',

                'surface' => '#ffffff',
                'background' => '#eff0f4',
                'text' => '#171717',
                'muted' => '#737373',
                'border' => '#e2e2e6',

                'sidebar_surface' => '#0b0813',
                'sidebar_text' => '#fafafa',
                'sidebar_muted' => '#a3a3a3',
                'sidebar_active_bg' => '#8b5cf6',
                'sidebar_active_text' => '#ffffff',
                'sidebar_active_dot' => '#c084fc',

                'header_bg' => '#ffffff',
                'header_border' => '#f5f5f5',
            ],
        ],

        // ═══════════════════════════════════════════════
        //  ESCURO (DARK MODE - Space Black)
        // ═══════════════════════════════════════════════
        'escuro' => [
            'name' => 'Escuro',
            'tokens' => [
                'primary_50' => '#030712',
                'primary_100' => '#0b0f19',
                'primary_200' => '#1e293b',
                'primary_300' => '#334155',
                'primary_400' => '#475569',
                'primary_500' => '#6366f1',
                'primary_600' => '#818cf8',
                'primary_700' => '#a5b4fc',
                'primary_800' => '#c7d2fe',
                'primary_900' => '#e0e7ff',

                'surface' => '#0b0f19',
                'background' => '#030712',
                'text' => '#f9fafb',
                'muted' => '#9ca3af',
                'border' => '#1f2937',

                'sidebar_surface' => '#0b0f19',
                'sidebar_text' => '#f9fafb',
                'sidebar_muted' => '#6b7280',
                'sidebar_active_bg' => '#1f2937',
                'sidebar_active_text' => '#6366f1',
                'sidebar_active_dot' => '#6366f1',

                'header_bg' => '#0b0f19',
                'header_border' => '#1f2937',

                'on_primary' => '#ffffff',
                'on_warning' => '#030712',
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->temas as $slug => $data) {
            // Merge semantic tokens, theme overrides take precedence
            $tokens = array_merge($this->semanticTokens, $data['tokens']);

            Theme::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'is_active' => true,
                    'tokens' => $tokens,
                ]
            );
        }

        // Clear cache so that the changes take effect immediately
        app(\App\Services\ThemeService::class)->clearCache();

        $this->command->info('5 temas atualizados com as novas cores!');
    }
}
