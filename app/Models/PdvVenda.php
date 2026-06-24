<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvVenda extends Model
{
    protected $table = 'pdv_vendas';

    protected $fillable = [
        'loja_id', 'caixa_abertura_id', 'usuario_id', 'cliente_id',
        'status', 'subtotal', 'desconto', 'acrescimo', 'total',
        'finalizada_at', 'fiscal_documento_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'desconto' => 'decimal:2',
            'acrescimo' => 'decimal:2', 'total' => 'decimal:2',
            'finalizada_at' => 'datetime',
        ];
    }

    public function itens()
    {
        return $this->hasMany(PdvVendaItem::class, 'venda_id');
    }

    public function pagamentos()
    {
        return $this->hasMany(PdvVendaPagamento::class, 'venda_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function caixaAbertura()
    {
        return $this->belongsTo(PdvCaixaAbertura::class, 'caixa_abertura_id');
    }

    public function devolucoes()
    {
        return $this->hasMany(PdvDevolucao::class, 'venda_id');
    }
}
