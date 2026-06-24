<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos_base', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos_base', 'aliquota_icms')) {
                $table->decimal('aliquota_icms', 7, 4)->nullable()->after('cst_icms');
            }
            if (!Schema::hasColumn('produtos_base', 'aliquota_pis')) {
                $table->decimal('aliquota_pis', 7, 4)->nullable()->after('aliquota_icms');
            }
            if (!Schema::hasColumn('produtos_base', 'aliquota_cofins')) {
                $table->decimal('aliquota_cofins', 7, 4)->nullable()->after('aliquota_pis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('produtos_base', function (Blueprint $table) {
            $table->dropColumn(['aliquota_icms', 'aliquota_pis', 'aliquota_cofins']);
        });
    }
};
