<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'loja_id', 'cliente_id', 'endereco_id', 'separador_id', 'entregador_id', 'codigo', 'origem',
        'tipo_entrega', 'status', 'forma_pagamento_id',
        'subtotal', 'desconto', 'taxa_entrega', 'total',
        'observacao_cliente', 'slot_inicio', 'slot_fim',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'desconto' => 'decimal:2',
            'taxa_entrega' => 'decimal:2',
            'total' => 'decimal:2',
            'slot_inicio' => 'datetime',
            'slot_fim' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function itens()
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(PedidoPagamento::class, 'pedido_id');
    }

    public function separador()
    {
        return $this->belongsTo(User::class, 'separador_id');
    }

    public function entregador()
    {
        return $this->belongsTo(User::class, 'entregador_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function endereco()
    {
        return $this->belongsTo(ClientesEndereco::class, 'endereco_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}
