<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoBase extends Model
{
    protected $table = 'produtos_base';

    protected $fillable = [
        'categoria_id', 'nome', 'slug', 'descricao', 'ativo',
        'ncm_id', 'cfop_id', 'cest_id', 'cst_icms', 'cst_pis', 'cst_cofins', 'origem_mercadoria',
        'aliquota_icms', 'aliquota_pis', 'aliquota_cofins',
        'unidade_medida_id', 'marca_id', 'embalagem_id',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
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

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'marca_id');
    }

    public function embalagem()
    {
        return $this->belongsTo(Embalagem::class, 'embalagem_id');
    }
}
