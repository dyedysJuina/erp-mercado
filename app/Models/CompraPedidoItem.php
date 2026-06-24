<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraPedidoItem extends Model
{
    protected $table = 'compras_pedido_itens';

    public $timestamps = false;

    protected $fillable = [
        'compra_pedido_id', 'produto_variacao_id',
        'quantidade_pedida', 'quantidade_recebida',
        'custo_unitario', 'total_item',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_pedida' => 'float',
            'quantidade_recebida' => 'float',
            'custo_unitario' => 'float',
            'total_item' => 'float',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(CompraPedido::class, 'compra_pedido_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
