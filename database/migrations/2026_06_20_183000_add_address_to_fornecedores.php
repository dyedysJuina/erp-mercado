<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->string('cep', 9)->nullable()->after('email');
            $table->string('logradouro', 255)->nullable()->after('cep');
            $table->string('numero', 20)->nullable()->after('logradouro');
            $table->string('bairro', 150)->nullable()->after('numero');
            $table->string('complemento', 255)->nullable()->after('bairro');
            $table->unsignedBigInteger('cidade_id')->nullable()->after('complemento');
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('SET NULL')->onUpdate('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->dropForeign(['cidade_id']);
            $table->dropColumn(['cep', 'logradouro', 'numero', 'bairro', 'complemento', 'cidade_id']);
        });
    }
};
