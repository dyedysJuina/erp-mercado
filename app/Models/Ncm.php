<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ncm extends Model
{
    protected $table = 'ncm';
    public $timestamps = false;
    protected $fillable = ['codigo', 'descricao', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
}
