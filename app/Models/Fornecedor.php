<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';

    protected $fillable = [
        'tipo_pessoa', 'razao_social', 'nome_fantasia', 'cnpj_cpf',
        'inscricao_estadual', 'telefone', 'email',
        'cep', 'logradouro', 'numero', 'bairro', 'complemento', 'cidade_id',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }

    public function contatos()
    {
        return $this->hasMany(FornecedorContato::class, 'fornecedor_id')->orderBy('principal', 'desc');
    }
}
