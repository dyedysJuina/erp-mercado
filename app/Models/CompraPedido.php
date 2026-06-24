<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraPedido extends Model
{
    protected $table = 'compras_pedidos';

    protected $fillable = [
        'loja_id', 'fornecedor_id', 'usuario_id', 'status',
        'total_produtos', 'valor_frete', 'valor_desconto',
        'total_pedido', 'previsao_entrega',
        'observacoes', 'condicao_pagamento', 'tipo_frete',
        'tipo_pedido', 'data_pedido',
    ];

    protected function casts(): array
    {
        return [
            'total_produtos' => 'float',
            'valor_frete' => 'float',
            'valor_desconto' => 'float',
            'total_pedido' => 'float',
            'previsao_entrega' => 'date',
            'data_pedido' => 'date',
        ];
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function itens()
    {
        return $this->hasMany(CompraPedidoItem::class, 'compra_pedido_id');
    }

    public function recebimentos()
    {
        return $this->hasMany(CompraRecebimento::class, 'compra_pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
