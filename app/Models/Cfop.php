<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cfop extends Model
{
    protected $table = 'cfop';
    public $timestamps = false;
    protected $fillable = ['codigo', 'descricao', 'aplicacao', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
}
