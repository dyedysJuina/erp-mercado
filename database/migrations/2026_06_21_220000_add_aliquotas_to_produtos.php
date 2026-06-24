<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produto_variacoes', function (Blueprint $table): void {
            if (!Schema::hasColumn('produto_variacoes', 'aliquota_icms')) {
                $table->decimal('aliquota_icms', 5, 2)->nullable()->comment('% ICMS');
            }
            if (!Schema::hasColumn('produto_variacoes', 'aliquota_pis')) {
                $table->decimal('aliquota_pis', 5, 2)->nullable()->comment('% PIS');
            }
            if (!Schema::hasColumn('produto_variacoes', 'aliquota_cofins')) {
                $table->decimal('aliquota_cofins', 5, 2)->nullable()->comment('% COFINS');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produto_variacoes', function (Blueprint $table): void {
            $table->dropColumn(['aliquota_icms', 'aliquota_pis', 'aliquota_cofins']);
        });
    }
};
