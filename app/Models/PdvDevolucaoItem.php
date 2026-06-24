<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvDevolucaoItem extends Model
{
    protected $table = 'pdv_devolucao_itens';

    public $timestamps = false;

    protected $fillable = [
        'devolucao_id', 'produto_variacao_id', 'quantidade', 'valor_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:3',
            'valor_unitario' => 'decimal:2',
        ];
    }

    public function devolucao()
    {
        return $this->belongsTo(PdvDevolucao::class, 'devolucao_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
