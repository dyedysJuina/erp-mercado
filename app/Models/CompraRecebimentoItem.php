<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraRecebimentoItem extends Model
{
    protected $table = 'compras_recebimento_itens';

    public $timestamps = false;

    protected $fillable = [
        'recebimento_id', 'produto_variacao_id',
        'quantidade_recebida', 'custo_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_recebida' => 'float',
            'custo_unitario' => 'float',
        ];
    }

    public function recebimento()
    {
        return $this->belongsTo(CompraRecebimento::class, 'recebimento_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
