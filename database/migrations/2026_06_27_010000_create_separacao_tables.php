<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pedidos_separacoes')) {
            Schema::create('pedidos_separacoes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedido_id')->unique();
                $table->unsignedBigInteger('separador_id')->nullable();
                $table->enum('status', ['aguardando', 'em_andamento', 'pausada', 'finalizada', 'cancelada'])->default('aguardando');
                $table->dateTime('inicio_at')->nullable();
                $table->dateTime('fim_at')->nullable();
                $table->mediumText('observacao')->nullable();
            });
        }

        if (!Schema::hasTable('pedidos_separacao_itens')) {
            Schema::create('pedidos_separacao_itens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('separacao_id');
                $table->unsignedBigInteger('pedido_item_id');
                $table->decimal('quantidade_separada', 12, 3)->nullable();
                $table->enum('status', ['pendente', 'separado', 'faltou', 'substituido'])->default('pendente');
                $table->string('observacao')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pedidos_status_historico')) {
            Schema::create('pedidos_status_historico', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pedido_id');
                $table->unsignedBigInteger('usuario_id')->nullable();
                $table->string('status_anterior', 50)->nullable();
                $table->string('status_novo', 50);
                $table->string('observacao')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_separacao_itens');
        Schema::dropIfExists('pedidos_separacoes');
        Schema::dropIfExists('pedidos_status_historico');
    }
};
