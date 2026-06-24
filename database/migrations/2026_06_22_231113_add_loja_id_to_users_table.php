<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'loja_id')) {
                $table->foreignId('loja_id')->nullable()->constrained('lojas')->nullOnDelete()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'loja_id')) {
                $table->dropForeign(['loja_id']);
                $table->dropColumn('loja_id');
            }
        });
    }
};
