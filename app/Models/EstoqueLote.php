<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueLote extends Model
{
    protected $table = 'estoque_lotes';

    protected $fillable = [
        'loja_id', 'produto_variacao_id', 'local_id', 'fornecedor_id',
        'numero_lote', 'data_fabricacao', 'data_validade',
        'quantidade_atual', 'custo_unitario',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_atual' => 'float',
            'custo_unitario' => 'float',
            'data_fabricacao' => 'date',
            'data_validade' => 'date',
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

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'fornecedor_id');
    }
}
