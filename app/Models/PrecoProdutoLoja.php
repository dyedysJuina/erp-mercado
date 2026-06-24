<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecoProdutoLoja extends Model
{
    protected $table = 'precos_produtos_lojas';

    protected $fillable = [
        'loja_id', 'produto_variacao_id',
        'preco_custo', 'custo_medio', 'margem_percentual',
        'preco_venda', 'preco_atacado', 'quantidade_min_atacado', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_custo' => 'decimal:4',
            'custo_medio' => 'decimal:4',
            'margem_percentual' => 'decimal:4',
            'preco_venda' => 'decimal:2',
            'preco_atacado' => 'decimal:2',
            'ativo' => 'boolean',
        ];
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
