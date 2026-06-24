<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produto_imagens', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('principal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('produto_imagens', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'created_at', 'updated_at']);
        });
    }
};
