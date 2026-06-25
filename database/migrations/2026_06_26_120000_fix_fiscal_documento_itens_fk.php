<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE fiscal_documento_itens ADD CONSTRAINT fiscal_documento_itens_fiscal_documento_id_foreign FOREIGN KEY (fiscal_documento_id) REFERENCES fiscal_documentos(id) ON DELETE CASCADE');
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Duplicate')) throw $e;
        }
    }

    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE fiscal_documento_itens DROP FOREIGN KEY fiscal_documento_itens_fiscal_documento_id_foreign');
        } catch (\Exception $e) {
            // ignore if doesn't exist
        }
    }
};
