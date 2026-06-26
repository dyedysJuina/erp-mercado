<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar whatsapp a users (para unificar login)
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'whatsapp')) {
            Schema::table('users', function (Blueprint $t) {
                $t->string('whatsapp', 30)->nullable()->unique()->after('email');
            });
            echo "  ✅ users.whatsapp adicionada\n";
        } else {
            echo "  ℹ️  users.whatsapp ja existe\n";
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'whatsapp')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('whatsapp');
            });
        }
    }
};
