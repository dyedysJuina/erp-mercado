<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueSaldo extends Model
{
    protected $table = 'estoque_saldos';

    protected $fillable = [
        'loja_id', 'produto_variacao_id', 'local_id', 'lote_id',
        'quantidade_atual', 'quantidade_reservada',
        'estoque_minimo', 'estoque_maximo',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_atual' => 'float',
            'quantidade_reservada' => 'float',
            'estoque_minimo' => 'float',
            'estoque_maximo' => 'float',
        ];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function loja()
    {
        return $this->belongsTo(Loja::class, 'loja_id');
    }

    public function local()
    {
        return $this->belongsTo(EstoqueLocal::class, 'local_id');
    }

    public function lote()
    {
        return $this->belongsTo(EstoqueLote::class, 'lote_id');
    }
}
