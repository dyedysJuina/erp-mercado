<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalDocumentoItem extends Model
{
    protected $table = 'fiscal_documento_itens';
    public $timestamps = false;

    protected $fillable = [
        'fiscal_documento_id', 'produto_variacao_id',
        'quantidade', 'valor_unitario', 'valor_total',
        'ncm', 'cfop', 'cst_icms_csosn',
        'aliquota_icms', 'valor_icms',
        'cst_pis', 'aliquota_pis', 'valor_pis',
        'cst_cofins', 'aliquota_cofins', 'valor_cofins',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'float',
            'valor_unitario' => 'float',
            'valor_total' => 'float',
            'aliquota_icms' => 'float',
            'valor_icms' => 'float',
            'aliquota_pis' => 'float',
            'valor_pis' => 'float',
            'aliquota_cofins' => 'float',
            'valor_cofins' => 'float',
        ];
    }

    public function documento()
    {
        return $this->belongsTo(FiscalDocumento::class, 'fiscal_documento_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
