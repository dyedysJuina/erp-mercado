<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IcmsCst extends Model
{
    protected $table = 'icms_cst';
    public $timestamps = false;
    protected $fillable = ['codigo', 'descricao', 'regime', 'ativo'];
    protected function casts(): array { return ['ativo' => 'boolean']; }
}
