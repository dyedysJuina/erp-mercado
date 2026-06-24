<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE produto_variacoes ADD FULLTEXT INDEX produto_variacoes_nome_completo_fulltext (nome_completo)');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), '1061')) throw $e;
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE produto_variacoes DROP INDEX produto_variacoes_nome_completo_fulltext');
    }
};
