<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoVariacao extends Model
{
    protected $table = 'produto_variacoes';

    protected $fillable = [
        'produto_base_id', 'marca_id', 'unidade_medida_id', 'embalagem_id',
        'nome_completo', 'slug', 'sku', 'conteudo_quantidade',
        'pesavel', 'fracionado', 'quantidade_minima_venda', 'passo_venda',
        'ncm', 'cest', 'origem_mercadoria', 'foto_capa_url', 'ativo',
        'ncm_id', 'cfop_id', 'cest_id', 'cst_icms', 'cst_pis', 'cst_cofins',
        'aliquota_icms', 'aliquota_pis', 'aliquota_cofins',
    ];

    protected function casts(): array
    {
        return [
            'conteudo_quantidade' => 'float',
            'pesavel' => 'boolean',
            'fracionado' => 'boolean',
            'quantidade_minima_venda' => 'float',
            'passo_venda' => 'float',
            'ativo' => 'boolean',
            'aliquota_icms' => 'float',
            'aliquota_pis' => 'float',
            'aliquota_cofins' => 'float',
        ];
    }

    public function produtoBase()
    {
        return $this->belongsTo(ProdutoBase::class, 'produto_base_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function embalagem()
    {
        return $this->belongsTo(Embalagem::class, 'embalagem_id');
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    public function codigosBarras()
    {
        return $this->hasMany(ProdutoCodigoBarras::class, 'produto_variacao_id');
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class, 'produto_variacao_id')->orderBy('ordem');
    }

    public function atributos()
    {
        return $this->hasMany(ProdutoVariacaoAtributo::class, 'produto_variacao_id');
    }

    public function apresentacoes()
    {
        return $this->hasMany(ProdutoApresentacao::class, 'produto_variacao_id');
    }

    public function ncm()
    {
        return $this->belongsTo(Ncm::class, 'ncm_id');
    }

    public function cfop()
    {
        return $this->belongsTo(Cfop::class, 'cfop_id');
    }

    public function cest()
    {
        return $this->belongsTo(Cest::class, 'cest_id');
    }

    public function precosTabela()
    {
        return $this->hasMany(TabelaPrecoItem::class, 'produto_variacao_id');
    }

    public function estoqueSaldos()
    {
        return $this->hasMany(EstoqueSaldo::class, 'produto_variacao_id');
    }

    public function vendaItens()
    {
        return $this->hasMany(PdvVendaItem::class, 'produto_variacao_id');
    }

    public function precosProdutosLoja()
    {
        return $this->hasMany(PrecoProdutoLoja::class, 'produto_variacao_id');
    }
}
