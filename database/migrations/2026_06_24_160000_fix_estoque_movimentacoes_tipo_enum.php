<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE estoque_movimentacoes MODIFY COLUMN tipo VARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE estoque_movimentacoes MODIFY COLUMN tipo ENUM('entrada_compra','saida_venda_pdv','saida_venda_online','perda','avaria','vencimento','ajuste','transferencia_entrada','transferencia_saida','reserva','baixa_reserva') NOT NULL");
    }
};
