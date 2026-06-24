<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ncm', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 8)->unique();
            $table->string('descricao', 500);
            $table->boolean('ativo')->default(true);
        });

        Schema::create('cfop', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 4)->unique();
            $table->string('descricao', 300);
            $table->string('aplicacao', 20)->comment('entrada ou saida');
            $table->boolean('ativo')->default(true);
        });

        Schema::create('cest', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 7)->unique();
            $table->string('descricao', 500);
            $table->string('segmento', 100)->nullable();
            $table->boolean('ativo')->default(true);
        });

        Schema::create('icms_cst', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 3)->unique();
            $table->string('descricao', 200);
            $table->string('regime', 20)->comment('simples_nacional ou normal');
            $table->boolean('ativo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ncm');
        Schema::dropIfExists('cfop');
        Schema::dropIfExists('cest');
        Schema::dropIfExists('icms_cst');
    }
};
