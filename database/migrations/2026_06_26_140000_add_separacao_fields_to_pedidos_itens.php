<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('pedidos_itens', 'quantidade_separada')) {
                $table->decimal('quantidade_separada', 12, 3)->default(0)->after('quantidade_solicitada');
            }
            if (!Schema::hasColumn('pedidos_itens', 'observacao_separacao')) {
                $table->text('observacao_separacao')->nullable()->after('status_item');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedidos_itens', function (Blueprint $table) {
            $table->dropColumn(['quantidade_separada', 'observacao_separacao']);
        });
    }
};
