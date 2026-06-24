<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceiroConta extends Model
{
    protected $table = 'financeiro_contas';

    public $timestamps = false;

    protected $fillable = ['empresa_id', 'nome', 'tipo', 'banco', 'agencia', 'conta', 'saldo_inicial', 'ativo'];

    protected function casts(): array
    {
        return ['saldo_inicial' => 'float', 'ativo' => 'boolean'];
    }
}
