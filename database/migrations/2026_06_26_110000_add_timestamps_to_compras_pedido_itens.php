<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras_pedido_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_pedido_itens', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('compras_pedido_itens', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
