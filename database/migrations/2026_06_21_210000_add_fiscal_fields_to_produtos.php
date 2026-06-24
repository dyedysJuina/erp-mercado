<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos_base', function (Blueprint $table): void {
            if (!Schema::hasColumn('produtos_base', 'ncm_id')) {
                $table->foreignId('ncm_id')->nullable()->constrained('ncm')->nullOnDelete();
            }
            if (!Schema::hasColumn('produtos_base', 'cfop_id')) {
                $table->foreignId('cfop_id')->nullable()->constrained('cfop')->nullOnDelete();
            }
            if (!Schema::hasColumn('produtos_base', 'cest_id')) {
                $table->foreignId('cest_id')->nullable()->constrained('cest')->nullOnDelete();
            }
            if (!Schema::hasColumn('produtos_base', 'cst_icms')) {
                $table->string('cst_icms', 3)->nullable();
            }
            if (!Schema::hasColumn('produtos_base', 'cst_pis')) {
                $table->string('cst_pis', 2)->nullable();
            }
            if (!Schema::hasColumn('produtos_base', 'cst_cofins')) {
                $table->string('cst_cofins', 2)->nullable();
            }
            if (!Schema::hasColumn('produtos_base', 'origem_mercadoria')) {
                $table->string('origem_mercadoria', 1)->nullable();
            }
        });

        Schema::table('produto_variacoes', function (Blueprint $table): void {
            if (!Schema::hasColumn('produto_variacoes', 'ncm_id')) {
                $table->foreignId('ncm_id')->nullable()->constrained('ncm')->nullOnDelete();
            }
            if (!Schema::hasColumn('produto_variacoes', 'cfop_id')) {
                $table->foreignId('cfop_id')->nullable()->constrained('cfop')->nullOnDelete();
            }
            if (!Schema::hasColumn('produto_variacoes', 'cest_id')) {
                $table->foreignId('cest_id')->nullable()->constrained('cest')->nullOnDelete();
            }
            if (!Schema::hasColumn('produto_variacoes', 'cst_icms')) {
                $table->string('cst_icms', 3)->nullable();
            }
            if (!Schema::hasColumn('produto_variacoes', 'cst_pis')) {
                $table->string('cst_pis', 2)->nullable();
            }
            if (!Schema::hasColumn('produto_variacoes', 'cst_cofins')) {
                $table->string('cst_cofins', 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('produto_variacoes', function (Blueprint $table): void {
            $table->dropForeign(['ncm_id']);
            $table->dropForeign(['cfop_id']);
            $table->dropForeign(['cest_id']);
            $table->dropColumn(['ncm_id', 'cfop_id', 'cest_id', 'cst_icms', 'cst_pis', 'cst_cofins']);
        });

        Schema::table('produtos_base', function (Blueprint $table): void {
            $table->dropForeign(['ncm_id']);
            $table->dropForeign(['cfop_id']);
            $table->dropForeign(['cest_id']);
            $table->dropColumn(['ncm_id', 'cfop_id', 'cest_id', 'cst_icms', 'cst_pis', 'cst_cofins', 'origem_mercadoria']);
        });
    }
};
