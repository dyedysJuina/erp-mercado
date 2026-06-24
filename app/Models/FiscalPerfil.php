<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalPerfil extends Model
{
    protected $table = 'fiscal_perfis';

    protected $fillable = [
        'nome', 'descricao',
        'cst_icms_csosn', 'aliquota_icms',
        'cst_pis', 'aliquota_pis',
        'cst_cofins', 'aliquota_cofins',
        'cfop_saida_padrao', 'cfop_entrada_padrao',
        'regime_tributario',
        'ambiente', 'certificado_path', 'certificado_senha',
        'serie_nfce', 'numero_nfce_atual',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'aliquota_icms' => 'float',
            'aliquota_pis' => 'float',
            'aliquota_cofins' => 'float',
            'ativo' => 'boolean',
        ];
    }
}
