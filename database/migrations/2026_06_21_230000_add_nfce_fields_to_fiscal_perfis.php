<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('fiscal_perfis', 'ambiente')) {
            Schema::table('fiscal_perfis', function (Blueprint $table): void {
                $table->string('ambiente', 1)->default('2')->comment('1=producao, 2=homologacao');
                $table->string('certificado_path', 500)->nullable();
                $table->string('certificado_senha', 100)->nullable();
                $table->string('serie_nfce', 3)->default('1');
                $table->string('numero_nfce_atual', 9)->default('1');
                $table->string('regime_tributario', 1)->default('1')->comment('1=SN, 2=LP, 3=LR');
            });
        }

        if (!Schema::hasColumn('fiscal_documentos', 'pdf_path')) {
            Schema::table('fiscal_documentos', function (Blueprint $table): void {
                $table->string('pdf_path', 500)->nullable()->after('xml_path');
                $table->text('motivo_cancelamento')->nullable()->after('pdf_path');
                $table->unsignedBigInteger('pdv_venda_id')->nullable()->after('fornecedor_id');
            });
        }

        if (Schema::hasTable('fiscal_perfis') && DB::table('fiscal_perfis')->count() === 0) {
            DB::table('fiscal_perfis')->insert([
                'nome' => 'Perfil Padrão - Simples Nacional',
                'descricao' => 'Configuração inicial para Simples Nacional em MT',
                'ambiente' => '2',
                'serie_nfce' => '1',
                'numero_nfce_atual' => '1',
                'regime_tributario' => '1',
                'cst_icms_csosn' => '102',
                'aliquota_icms' => 0,
                'cst_pis' => '49',
                'aliquota_pis' => 0,
                'cst_cofins' => '49',
                'aliquota_cofins' => 0,
                'cfop_saida_padrao' => '5102',
                'cfop_entrada_padrao' => '1102',
                'ativo' => true,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('fiscal_perfis', function (Blueprint $table): void {
            $table->dropColumn(['ambiente', 'certificado_path', 'certificado_senha', 'serie_nfce', 'numero_nfce_atual', 'regime_tributario']);
        });
        Schema::table('fiscal_documentos', function (Blueprint $table): void {
            $table->dropColumn(['pdf_path', 'motivo_cancelamento', 'pdv_venda_id']);
        });
    }
};
