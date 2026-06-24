<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmGruposCliente extends Model
{
    protected $table = 'crm_grupos_clientes';

    public $timestamps = false;

    protected $fillable = ['empresa_id', 'nome', 'descricao', 'ativo'];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function clientes()
    {
        return $this->belongsToMany(Cliente::class, 'crm_clientes_grupos', 'grupo_id', 'cliente_id');
    }
}
