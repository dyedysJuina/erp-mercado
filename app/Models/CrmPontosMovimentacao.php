<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmPontosMovimentacao extends Model
{
    protected $table = 'crm_pontos_movimentacoes';

    protected $fillable = ['cliente_id', 'pedido_id', 'tipo', 'pontos', 'descricao'];

    protected function casts(): array
    {
        return ['pontos' => 'integer'];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
