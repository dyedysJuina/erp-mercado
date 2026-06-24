<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegraFiscalNcm extends Model
{
    protected $table = 'regras_fiscais_ncm';
    public $timestamps = false;

    protected $fillable = ['ncm_prefix', 'csosn', 'descricao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public static function sugerirCsosn(string $ncmCodigo): string
    {
        $ncm = trim($ncmCodigo);
        if (strlen($ncm) < 4) return '102';
        $prefixo4 = substr($ncm, 0, 4);
        $prefixo2 = substr($ncm, 0, 2);

        $regra = self::where('ncm_prefix', $prefixo4)->where('ativo', true)->first();
        if ($regra) return $regra->csosn;

        $regra2 = self::where('ncm_prefix', $prefixo2 . 'xx')->where('ativo', true)->first();
        if ($regra2) return $regra2->csosn;

        $regra3 = self::where('ncm_prefix', $prefixo2)->where('ativo', true)->first();
        if ($regra3) return $regra3->csosn;

        return '102';
    }
}
