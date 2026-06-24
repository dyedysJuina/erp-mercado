<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvVendaPagamento extends Model
{
    protected $table = 'pdv_venda_pagamentos';

    public $timestamps = false;

    protected $fillable = ['venda_id', 'forma_pagamento_id', 'valor', 'parcelas'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2', 'parcelas' => 'integer'];
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}
