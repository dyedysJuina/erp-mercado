<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            if (!Schema::hasColumn('clientes', 'password')) {
                $table->string('password', 255)->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('clientes', 'remember_token')) {
                $table->rememberToken()->after('password');
            }
            if (!Schema::hasColumn('clientes', 'ultimo_login_at')) {
                $table->timestamp('ultimo_login_at')->nullable()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['password', 'remember_token', 'ultimo_login_at']);
        });
    }
};
