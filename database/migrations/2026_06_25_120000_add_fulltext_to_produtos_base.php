<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE produtos_base ADD FULLTEXT INDEX produtos_base_nome_fulltext (nome)');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), '1061')) throw $e;
        }
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE produtos_base DROP INDEX produtos_base_nome_fulltext');
    }
};
