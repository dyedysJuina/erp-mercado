<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecoHistorico extends Model
{
    protected $table = 'precos_historico';

    protected $fillable = [
        'loja_id', 'produto_variacao_id', 'usuario_id',
        'preco_anterior', 'preco_novo', 'motivo',
    ];

    protected function casts(): array
    {
        return [
            'preco_anterior' => 'decimal:2',
            'preco_novo' => 'decimal:2',
        ];
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
