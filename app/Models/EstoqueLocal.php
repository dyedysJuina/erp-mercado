<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueLocal extends Model
{
    protected $table = 'estoque_locais';

    public $timestamps = false;

    protected $fillable = ['loja_id', 'nome', 'tipo', 'corredor', 'prateleira', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }
}
