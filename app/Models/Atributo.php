<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atributo extends Model
{
    protected $fillable = [
        'nome', 'slug', 'tipo',
        'unidade_medida_id', 'opcoes', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'opcoes' => 'array',
            'ativo' => 'boolean',
        ];
    }

    public function unidadeMedida()
    {
        return $this->belongsTo(UnidadeMedida::class, 'unidade_medida_id');
    }
}
