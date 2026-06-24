<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabelaPreco extends Model
{
    protected $fillable = ['nome', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function itens()
    {
        return $this->hasMany(TabelaPrecoItem::class, 'tabela_preco_id');
    }

    public function lojas()
    {
        return $this->hasMany(Loja::class, 'tabela_preco_id');
    }
}
