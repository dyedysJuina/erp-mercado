<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvCaixaAbertura extends Model
{
    protected $table = 'pdv_caixas_aberturas';

    protected $fillable = [
        'caixa_id', 'usuario_id', 'status', 'valor_abertura',
        'valor_fechamento_informado', 'valor_fechamento_sistema', 'diferenca',
        'aberto_at', 'fechado_at',
    ];

    protected function casts(): array
    {
        return [
            'valor_abertura' => 'decimal:2',
            'valor_fechamento_informado' => 'decimal:2',
            'valor_fechamento_sistema' => 'decimal:2',
            'diferenca' => 'decimal:2',
            'aberto_at' => 'datetime',
            'fechado_at' => 'datetime',
        ];
    }

    public function movimentos()
    {
        return $this->hasMany(PdvCaixaMovimento::class, 'caixa_abertura_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
