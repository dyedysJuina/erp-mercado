<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoApresentacao extends Model
{
    protected $table = 'produto_apresentacoes';

    protected $fillable = [
        'produto_variacao_id', 'embalagem_id', 'unidade_medida_id',
        'nome', 'tipo', 'conteudo_quantidade', 'fator_conversao_estoque',
        'permite_venda', 'permite_compra', 'controla_estoque',
        'principal_venda', 'principal_compra', 'principal_estoque', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'conteudo_quantidade' => 'float',
            'fator_conversao_estoque' => 'float',
            'permite_venda' => 'boolean',
            'permite_compra' => 'boolean',
            'controla_estoque' => 'boolean',
            'principal_venda' => 'boolean',
            'principal_compra' => 'boolean',
            'principal_estoque' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function embalagem()
    {
        return $this->belongsTo(Embalagem::class, 'embalagem_id');
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }
}
