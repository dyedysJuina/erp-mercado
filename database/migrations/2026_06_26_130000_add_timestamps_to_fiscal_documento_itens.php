<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fiscal_documento_itens', function (Blueprint $table) {
            if (!Schema::hasColumn('fiscal_documento_itens', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fiscal_documento_itens', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
