<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produto_variacoes', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->unique()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('produto_variacoes', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
    }
};
