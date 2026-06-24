<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueMovimentacao extends Model
{
    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'loja_id', 'produto_variacao_id', 'lote_id', 'usuario_id',
        'origem_tipo', 'origem_id',
        'tipo', 'quantidade', 'custo_unitario', 'justificativa',
    ];

    protected function casts(): array
    {
        return ['quantidade' => 'float'];
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
