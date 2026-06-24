<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesEndereco extends Model
{
    protected $table = 'clientes_enderecos';

    public $timestamps = false;

    protected $fillable = [
        'cliente_id', 'titulo', 'cidade_id',
        'cep', 'bairro', 'logradouro', 'numero', 'complemento', 'referencia',
        'latitude', 'longitude', 'principal', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'principal' => 'boolean',
            'ativo' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cidade()
    {
        return $this->belongsTo(Cidade::class, 'cidade_id');
    }
}
