<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoSeparacao extends Model
{
    protected $table = 'pedidos_separacoes';

    protected $fillable = [
        'pedido_id', 'separador_id', 'status',
        'inicio_at', 'fim_at', 'observacao',
    ];

    protected function casts(): array
    {
        return [
            'inicio_at' => 'datetime',
            'fim_at' => 'datetime',
        ];
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function separador()
    {
        return $this->belongsTo(User::class, 'separador_id');
    }

    public function itens()
    {
        return $this->hasMany(PedidoSeparacaoItem::class, 'separacao_id');
    }
}
