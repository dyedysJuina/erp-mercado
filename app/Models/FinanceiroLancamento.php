<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceiroLancamento extends Model
{
    protected $table = 'financeiro_lancamentos';

    protected $fillable = [
        'empresa_id', 'loja_id', 'categoria_id', 'centro_custo_id', 'conta_id',
        'tipo', 'descricao', 'valor',
        'data_competencia', 'data_vencimento', 'data_pagamento',
        'status', 'observacao',
        'pdv_venda_id', 'pedido_id', 'compra_pedido_id',
        'reconcilied_at', 'reconcilied_by',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'float',
            'data_competencia' => 'date',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
            'reconcilied_at' => 'datetime',
        ];
    }

    public function reconciliedBy()
    {
        return $this->belongsTo(User::class, 'reconcilied_by');
    }

    public function categoria()
    {
        return $this->belongsTo(FinanceiroCategoria::class, 'categoria_id');
    }

    public function centroCusto()
    {
        return $this->belongsTo(FinanceiroCentroCusto::class, 'centro_custo_id');
    }

    public function conta()
    {
        return $this->belongsTo(FinanceiroConta::class, 'conta_id');
    }

    public function pdvVenda()
    {
        return $this->belongsTo(PdvVenda::class, 'pdv_venda_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
