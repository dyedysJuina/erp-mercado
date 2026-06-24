<?php

namespace Tests\Feature;

use App\Livewire\MarcaManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MarcaLiveSearchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('marcas', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120);
            $table->string('slug', 150)->unique();
            $table->string('logo_url')->nullable();
            $table->boolean('ativo')->default(true);
        });

        DB::table('marcas')->insert([
            ['id' => 1, 'nome' => 'Masson', 'slug' => 'masson', 'ativo' => true],
            ['id' => 2, 'nome' => 'Tio Urbano', 'slug' => 'tio-urbano', 'ativo' => true],
            ['id' => 3, 'nome' => 'Colgate', 'slug' => 'colgate', 'ativo' => false],
        ]);
    }

    public function test_suggestions_only_start_with_two_characters(): void
    {
        $component = Livewire::test(MarcaManager::class)
            ->set('nome', 'M');

        $this->assertSame([], $component->get('sugestoesNome'));

        $component->set('nome', 'Mas')
            ->assertSet('nome', 'Mas')
            ->assertSet('slug', 'mas')
            ->assertSee('Masson');

        $this->assertSame(['Masson'], array_column($component->get('sugestoesNome'), 'nome'));
    }

    public function test_repeated_name_is_detected_without_changing_what_was_typed(): void
    {
        Livewire::test(MarcaManager::class)
            ->set('nome', 'Tio Urbano')
            ->assertSet('nome', 'Tio Urbano')
            ->assertSet('slug', 'tio-urbano')
            ->assertSet('duplicata', 'Tio Urbano')
            ->assertSee('Esta marca já está cadastrada.');
    }

    public function test_registration_preserves_the_brand_capitalization(): void
    {
        Livewire::test(MarcaManager::class)
            ->set('nome', 'iFood   Mercado')
            ->call('cadastrar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('marcas', [
            'nome' => 'iFood Mercado',
            'slug' => 'ifood-mercado',
        ]);
    }
}
