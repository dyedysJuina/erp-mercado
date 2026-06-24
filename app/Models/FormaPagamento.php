<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    protected $table = 'formas_pagamento';

    public $timestamps = false;

    protected $fillable = ['nome', 'tipo', 'taxa_percentual', 'prazo_recebimento_dias', 'ativo'];

    protected function casts(): array
    {
        return [
            'taxa_percentual' => 'decimal:4',
            'prazo_recebimento_dias' => 'integer',
            'ativo' => 'boolean',
        ];
    }

    public function pdvPagamentos()
    {
        return $this->hasMany(PdvVendaPagamento::class, 'forma_pagamento_id');
    }

    public function pedidoPagamentos()
    {
        return $this->hasMany(PedidoPagamento::class, 'forma_pagamento_id');
    }
}
