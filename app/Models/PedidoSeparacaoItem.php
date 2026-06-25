<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoSeparacaoItem extends Model
{
    protected $table = 'pedidos_separacao_itens';

    protected $fillable = [
        'separacao_id', 'pedido_item_id',
        'quantidade_separada', 'status', 'observacao',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_separada' => 'decimal:3',
        ];
    }

    public function separacao()
    {
        return $this->belongsTo(PedidoSeparacao::class, 'separacao_id');
    }

    public function pedidoItem()
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_id');
    }
}
