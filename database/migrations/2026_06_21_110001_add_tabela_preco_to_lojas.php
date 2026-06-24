<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->foreignId('tabela_preco_id')->nullable()->constrained('tabela_precos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table) {
            $table->dropForeign(['tabela_preco_id']);
            $table->dropColumn('tabela_preco_id');
        });
    }
};
