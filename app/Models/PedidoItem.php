<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedidos_itens';

    public $timestamps = false;

    protected $fillable = [
        'pedido_id', 'produto_variacao_id', 'oferta_produto_id',
        'quantidade_solicitada', 'quantidade_atendida',
        'quantidade_separada', 'preco_unitario', 'total_item',
        'status_item', 'substituto_produto_variacao_id',
        'observacao_cliente', 'observacao_separador',
        'observacao_separacao', 'separado_por',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_solicitada' => 'decimal:3',
            'quantidade_atendida' => 'decimal:3',
            'preco_unitario' => 'decimal:2',
            'total_item' => 'decimal:2',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function substituto()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'substituto_produto_variacao_id');
    }
}
