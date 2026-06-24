<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfertaCampanha extends Model
{
    protected $table = 'ofertas_campanhas';

    protected $fillable = [
        'empresa_id', 'nome', 'descricao', 'data_inicio', 'data_fim', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'datetime',
            'data_fim' => 'datetime',
            'ativo' => 'boolean',
        ];
    }

    public function produtos()
    {
        return $this->hasMany(OfertaProduto::class, 'campanha_id');
    }

    public function lojas()
    {
        return $this->belongsToMany(Loja::class, 'ofertas_campanhas_lojas', 'campanha_id', 'loja_id');
    }
}
