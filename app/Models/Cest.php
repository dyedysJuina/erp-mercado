<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cest extends Model
{
    protected $table = 'cest';
    public $timestamps = false;
    protected $fillable = ['codigo', 'descricao', 'segmento', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
}
