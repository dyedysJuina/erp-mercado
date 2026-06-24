<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraRecebimento extends Model
{
    protected $table = 'compras_recebimentos';

    protected $fillable = [
        'compra_pedido_id', 'loja_id', 'fornecedor_id', 'usuario_id',
        'numero_nota', 'chave_nfe', 'status',
    ];

    protected function casts(): array
    {
        return ['status' => 'string'];
    }

    public function pedido()
    {
        return $this->belongsTo(CompraPedido::class, 'compra_pedido_id');
    }

    public function itens()
    {
        return $this->hasMany(CompraRecebimentoItem::class, 'recebimento_id');
    }
}
