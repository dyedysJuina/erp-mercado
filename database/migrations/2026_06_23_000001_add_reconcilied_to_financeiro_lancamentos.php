<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            if (!Schema::hasColumn('financeiro_lancamentos', 'reconcilied_at')) {
                $table->timestamp('reconcilied_at')->nullable()->after('data_pagamento');
            }
            if (!Schema::hasColumn('financeiro_lancamentos', 'reconcilied_by')) {
                $table->unsignedBigInteger('reconcilied_by')->nullable()->after('reconcilied_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('financeiro_lancamentos', function (Blueprint $table) {
            $table->dropColumn(['reconcilied_at', 'reconcilied_by']);
        });
    }
};
