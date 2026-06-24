<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FornecedorContato extends Model
{
    protected $table = 'fornecedores_contatos';

    public $timestamps = false;

    protected $fillable = [
        'fornecedor_id', 'nome', 'cargo', 'telefone', 'email', 'principal',
    ];

    protected function casts(): array
    {
        return ['principal' => 'boolean'];
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }
}
