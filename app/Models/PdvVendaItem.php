<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvVendaItem extends Model
{
    protected $table = 'pdv_venda_itens';

    public $timestamps = false;

    protected $fillable = [
        'venda_id', 'produto_variacao_id',
        'quantidade', 'preco_unitario', 'desconto', 'total_item', 'cancelado',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:3', 'preco_unitario' => 'decimal:2',
            'desconto' => 'decimal:2', 'total_item' => 'decimal:2',
            'cancelado' => 'boolean',
        ];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
