<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('produto_variacao_atributos')) {
            Schema::create('produto_variacao_atributos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('produto_variacao_id')->constrained('produto_variacoes')->onDelete('cascade');
                $table->foreignId('atributo_id')->constrained('atributos')->onDelete('cascade');
                $table->string('valor_texto', 255)->nullable();
                $table->decimal('valor_numero', 14, 4)->nullable();
                $table->boolean('valor_booleano')->nullable();
                $table->date('valor_data')->nullable();
                $table->foreignId('unidade_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();
                $table->timestamps();
                $table->unique(['produto_variacao_id', 'atributo_id'], 'pv_atributo_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_variacao_atributos');
    }
};
