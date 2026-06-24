<?php

namespace Tests\Feature;

use App\Livewire\ThemeSwitcher;
use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ThemePersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_theme_created_in_the_database_generates_dynamic_css(): void
    {
        Theme::create([
            'slug' => 'personalizado',
            'name' => 'Personalizado',
            'is_active' => true,
            'tokens' => $this->tokens('#123456'),
        ]);

        $css = app(ThemeService::class)->getAllOverridesCss();

        $this->assertStringContainsString("[data-theme='personalizado']", $css);
        $this->assertStringContainsString('--primary-500: #123456;', $css);
    }

    public function test_the_selected_theme_is_persisted_for_the_user(): void
    {
        Theme::create([
            'slug' => 'personalizado',
            'name' => 'Personalizado',
            'is_active' => true,
            'tokens' => $this->tokens('#654321'),
        ]);

        $user = User::factory()->create(['theme' => 'verde']);

        Livewire::actingAs($user)
            ->test(ThemeSwitcher::class)
            ->call('setTheme', 'personalizado')
            ->assertSet('current', 'personalizado');

        $this->assertSame('personalizado', $user->refresh()->theme);

        $this->withoutVite();
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('data-theme="personalizado"', false)
            ->assertSee('--primary-500: #654321;', false);
    }

    private function tokens(string $primary): array
    {
        return [
            'primary_500' => $primary,
            'on_primary' => '#ffffff',
        ];
    }
}
