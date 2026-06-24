<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoCodigoBarras extends Model
{
    protected $table = 'produto_codigos_barras';

    public $timestamps = false;

    protected $fillable = [
        'produto_variacao_id', 'produto_apresentacao_id',
        'codigo', 'tipo', 'descricao', 'principal',
    ];

    protected function casts(): array
    {
        return ['principal' => 'boolean'];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }
}
