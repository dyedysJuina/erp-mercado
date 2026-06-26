<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'aceita_marketing' => 'boolean',
            'data_nascimento' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function enderecos()
    {
        return $this->hasMany(ClientesEndereco::class, 'cliente_id');
    }

    public function enderecoPrincipal()
    {
        return $this->hasOne(ClientesEndereco::class, 'cliente_id')->where('principal', true);
    }

    public function pdvVendas()
    {
        return $this->hasMany(PdvVenda::class, 'cliente_id');
    }

    public function pedidosStorefront()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function fiscalDocumentos()
    {
        return $this->hasMany(FiscalDocumento::class, 'cliente_id');
    }

    public function grupos()
    {
        return $this->belongsToMany(CrmGruposCliente::class, 'crm_clientes_grupos', 'cliente_id', 'grupo_id');
    }

    public function pontosMovimentacoes()
    {
        return $this->hasMany(CrmPontosMovimentacao::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
