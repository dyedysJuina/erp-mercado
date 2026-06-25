<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoPagamento extends Model
{
    protected $table = 'pedidos_pagamentos';

    public $timestamps = false;

    protected $fillable = [
        'pedido_id', 'forma_pagamento_id', 'status',
        'valor', 'gateway', 'transacao_id', 'pago_at',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'pago_at' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}
