<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Embalagem extends Model
{
    use SoftDeletes;

    protected $table = 'embalagens';

    protected $fillable = ['nome', 'sigla', 'ativo'];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }
}
