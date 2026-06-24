<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    public $timestamps = false;

    protected $fillable = ['estado_id', 'nome', 'codigo_ibge'];

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function getNomeCompletoAttribute(): string
    {
        return $this->nome . ($this->estado ? '/' . $this->estado->uf : '');
    }
}
