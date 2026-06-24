<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regras_fiscais_ncm', function (Blueprint $table): void {
            $table->id();
            $table->string('ncm_prefix', 4)->unique()->comment('4 primeiros digitos do NCM (capitulo)');
            $table->string('csosn', 3)->default('102')->comment('CSOSN do Simples Nacional');
            $table->string('descricao', 200)->nullable();
            $table->boolean('ativo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regras_fiscais_ncm');
    }
};
