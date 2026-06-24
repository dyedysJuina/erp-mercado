<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lojas', function (Blueprint $table): void {
            if (!Schema::hasColumn('lojas', 'inscricao_estadual')) {
                $table->string('inscricao_estadual', 20)->nullable()->after('cnpj');
            }
            if (!Schema::hasColumn('lojas', 'nome_fantasia')) {
                $table->string('nome_fantasia', 150)->nullable()->after('nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lojas', function (Blueprint $table): void {
            $table->dropColumn(['inscricao_estadual', 'nome_fantasia']);
        });
    }
};
