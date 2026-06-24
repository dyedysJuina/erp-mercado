<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvDevolucao extends Model
{
    protected $table = 'pdv_devolucoes';

    protected $fillable = [
        'venda_id', 'usuario_id', 'motivo', 'valor_total',
    ];

    protected function casts(): array
    {
        return ['valor_total' => 'decimal:2'];
    }

    public function itens()
    {
        return $this->hasMany(PdvDevolucaoItem::class, 'devolucao_id');
    }

    public function venda()
    {
        return $this->belongsTo(PdvVenda::class, 'venda_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
