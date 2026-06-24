<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';

    public $timestamps = false;

    protected $fillable = ['nome', 'slug', 'logo_url', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }
}
