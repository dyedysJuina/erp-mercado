<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabela_precos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tabela_precos_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabela_preco_id')->constrained('tabela_precos')->cascadeOnDelete();
            $table->foreignId('produto_variacao_id')->constrained('produto_variacoes')->cascadeOnDelete();
            $table->decimal('preco_custo', 12, 4)->default(0);
            $table->decimal('margem_percentual', 8, 4)->default(0);
            $table->decimal('preco_venda', 12, 2)->default(0);
            $table->decimal('preco_atacado', 12, 2)->nullable();
            $table->unique(['tabela_preco_id', 'produto_variacao_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabela_precos_itens');
        Schema::dropIfExists('tabela_precos');
    }
};
