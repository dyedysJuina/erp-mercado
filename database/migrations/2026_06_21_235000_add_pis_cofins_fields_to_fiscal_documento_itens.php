<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_documento_itens', function (Blueprint $table): void {
            if (!Schema::hasColumn('fiscal_documento_itens', 'valor_icms')) {
                $table->decimal('valor_icms', 12, 2)->default(0)->after('aliquota_icms');
                $table->string('cst_pis', 4)->nullable()->after('valor_icms');
                $table->decimal('aliquota_pis', 7, 4)->default(0)->after('cst_pis');
                $table->decimal('valor_pis', 12, 2)->default(0)->after('aliquota_pis');
                $table->string('cst_cofins', 4)->nullable()->after('valor_pis');
                $table->decimal('aliquota_cofins', 7, 4)->default(0)->after('cst_cofins');
                $table->decimal('valor_cofins', 12, 2)->default(0)->after('aliquota_cofins');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_documento_itens', function (Blueprint $table): void {
            $table->dropColumn(['valor_icms', 'cst_pis', 'aliquota_pis', 'valor_pis', 'cst_cofins', 'aliquota_cofins', 'valor_cofins']);
        });
    }
};
