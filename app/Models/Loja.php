<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loja extends Model
{
    protected $fillable = [
        'empresa_id', 'nome', 'nome_fantasia', 'codigo_interno', 'cnpj', 'inscricao_estadual', 'tipo',
        'telefone', 'email', 'cidade_id',
        'cep', 'bairro', 'logradouro', 'numero', 'complemento',
        'latitude', 'longitude', 'status_operacional', 'ativo',
        'tabela_preco_id',
    ];

    protected function casts(): array
    {
        return ['ativo' => 'boolean'];
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function tabelaPreco()
    {
        return $this->belongsTo(TabelaPreco::class, 'tabela_preco_id');
    }

    public function vendas()
    {
        return $this->hasMany(PdvVenda::class, 'loja_id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'loja_id');
    }

    public function estoqueSaldos()
    {
        return $this->hasMany(EstoqueSaldo::class, 'loja_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'loja_id');
    }
}
