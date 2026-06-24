<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DadosPadraoSeeder extends Seeder
{
    public function run(): void
    {
        $unidades = [
            ['sigla' => 'KG', 'nome' => 'Quilograma', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'G', 'nome' => 'Grama', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'MG', 'nome' => 'Miligrama', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'L', 'nome' => 'Litro', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'ML', 'nome' => 'Mililitro', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'UN', 'nome' => 'Unidade', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'CX', 'nome' => 'Caixa', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'PCT', 'nome' => 'Pacote', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'FD', 'nome' => 'Fardo', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'PC', 'nome' => 'Peça', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'DZ', 'nome' => 'Dúzia', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'T', 'nome' => 'Tonelada', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'M', 'nome' => 'Metro', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'CM', 'nome' => 'Centímetro', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'M2', 'nome' => 'Metro Quadrado', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'M3', 'nome' => 'Metro Cúbico', 'permite_decimal' => true, 'deleted_at' => null],
            ['sigla' => 'PR', 'nome' => 'Par', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'LT', 'nome' => 'Lata', 'permite_decimal' => false, 'deleted_at' => null],
            ['sigla' => 'GT', 'nome' => 'Garrafa', 'permite_decimal' => false, 'deleted_at' => null],
        ];

        foreach ($unidades as $u) {
            DB::table('unidades_medida')->updateOrInsert(
                ['sigla' => $u['sigla']],
                $u
            );
        }

        $embalagens = [
            ['nome' => 'Unidade', 'sigla' => 'UN', 'ativo' => true],
            ['nome' => 'Pacote', 'sigla' => 'PCT', 'ativo' => true],
            ['nome' => 'Caixa', 'sigla' => 'CX', 'ativo' => true],
            ['nome' => 'Fardo', 'sigla' => 'FD', 'ativo' => true],
            ['nome' => 'Pack', 'sigla' => 'PK', 'ativo' => true],
            ['nome' => 'Garrafa', 'sigla' => 'GT', 'ativo' => true],
            ['nome' => 'Lata', 'sigla' => 'LT', 'ativo' => true],
            ['nome' => 'Saco', 'sigla' => 'SC', 'ativo' => true],
            ['nome' => 'Cesta', 'sigla' => 'CST', 'ativo' => true],
            ['nome' => 'Bandeja', 'sigla' => 'BD', 'ativo' => true],
            ['nome' => 'Balde', 'sigla' => 'BLD', 'ativo' => true],
            ['nome' => 'Tambor', 'sigla' => 'TMB', 'ativo' => true],
            ['nome' => 'Pet', 'sigla' => 'PET', 'ativo' => true],
            ['nome' => 'Display', 'sigla' => 'DISP', 'ativo' => true],
        ];

        foreach ($embalagens as $e) {
            DB::table('embalagens')->updateOrInsert(
                ['nome' => $e['nome']],
                $e
            );
        }

        $this->command->info('Unidades e embalagens padronizadas criadas/atualizadas.');
    }
}
