<?php

namespace Tests\Feature;

use App\Services\PriceTableService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PriceTableServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tabela_precos_itens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tabela_preco_id');
            $table->decimal('preco_custo', 12, 4)->default(0);
            $table->decimal('margem_percentual', 8, 4)->default(0);
            $table->decimal('preco_venda', 12, 2)->default(0);
            $table->decimal('preco_atacado', 12, 2)->default(0);
            $table->timestamps();
        });

        DB::table('tabela_precos_itens')->insert([
            ['id' => 1, 'tabela_preco_id' => 10],
            ['id' => 2, 'tabela_preco_id' => 20],
        ]);
    }

    public function test_it_saves_brazilian_values_in_the_selected_table(): void
    {
        $saved = app(PriceTableService::class)->saveItems(10, [[
            'id' => 1,
            'c' => 'R$ 1.234,5678',
            'm' => '25,5000%',
            'v' => 'R$ 1.549,88',
            'a' => 'R$ 1.400,00',
        ]]);

        $item = DB::table('tabela_precos_itens')->find(1);

        $this->assertSame(1, $saved);
        $this->assertEquals(1234.5678, $item->preco_custo);
        $this->assertEquals(25.5, $item->margem_percentual);
        $this->assertEquals(1549.88, $item->preco_venda);
        $this->assertEquals(1400, $item->preco_atacado);
    }

    public function test_it_refuses_an_item_from_another_table(): void
    {
        $this->expectException(ValidationException::class);

        app(PriceTableService::class)->saveItems(10, [[
            'id' => 2,
            'c' => '10,00',
            'm' => '20,00',
            'v' => '12,00',
            'a' => '11,00',
        ]]);
    }
}
