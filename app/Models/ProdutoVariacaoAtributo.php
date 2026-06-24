<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdutoVariacaoAtributo extends Model
{
    protected $table = 'produto_variacao_atributos';

    protected $fillable = [
        'produto_variacao_id', 'atributo_id',
        'valor_texto', 'valor_numero', 'valor_booleano', 'valor_data',
        'unidade_medida_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_numero' => 'decimal:4',
            'valor_booleano' => 'boolean',
            'valor_data' => 'date',
        ];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function atributo()
    {
        return $this->belongsTo(Atributo::class, 'atributo_id');
    }
}
