<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pdv_venda_pagamentos', function (Blueprint $table) {
            $table->timestamp('cancelado_at')->nullable()->after('parcelas');
        });
    }

    public function down(): void
    {
        Schema::table('pdv_venda_pagamentos', function (Blueprint $table) {
            $table->dropColumn('cancelado_at');
        });
    }
};
