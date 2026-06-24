<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfertaProduto extends Model
{
    protected $table = 'ofertas_produtos';

    public $timestamps = false;

    protected $fillable = [
        'campanha_id', 'loja_id', 'produto_variacao_id',
        'preco_de', 'preco_por', 'preco_clube',
        'estoque_promocional', 'limite_por_cliente', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_de' => 'decimal:2',
            'preco_por' => 'decimal:2',
            'preco_clube' => 'decimal:2',
            'ativo' => 'boolean',
        ];
    }

    public function campanha()
    {
        return $this->belongsTo(OfertaCampanha::class, 'campanha_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
