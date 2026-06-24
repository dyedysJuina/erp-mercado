<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceiroCentroCusto extends Model
{
    protected $table = 'financeiro_centros_custo';

    public $timestamps = false;

    protected $fillable = ['empresa_id', 'nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }
}
