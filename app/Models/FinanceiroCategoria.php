<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceiroCategoria extends Model
{
    protected $table = 'financeiro_categorias';

    public $timestamps = false;

    protected $fillable = ['empresa_id', 'parent_id', 'tipo', 'nome', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
