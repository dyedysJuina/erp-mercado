<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaPrecoItem extends Model
{
    protected $table = 'tabela_precos_itens';

    protected $fillable = [
        'tabela_preco_id', 'produto_variacao_id',
        'preco_custo', 'margem_percentual', 'preco_venda', 'preco_atacado',
    ];

    protected function casts(): array
    {
        return [
            'preco_custo' => 'decimal:4',
            'margem_percentual' => 'decimal:4',
            'preco_venda' => 'decimal:2',
            'preco_atacado' => 'decimal:2',
        ];
    }

    public function tabela()
    {
        return $this->belongsTo(TabelaPreco::class, 'tabela_preco_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
