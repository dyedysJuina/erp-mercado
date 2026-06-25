<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoStatusHistorico extends Model
{
    protected $table = 'pedidos_status_historico';

    protected $fillable = [
        'pedido_id', 'usuario_id',
        'status_anterior', 'status_novo', 'observacao',
    ];
}
