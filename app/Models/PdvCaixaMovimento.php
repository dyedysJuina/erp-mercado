<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdvCaixaMovimento extends Model
{
    protected $table = 'pdv_caixa_movimentos';

    protected $fillable = [
        'caixa_abertura_id', 'usuario_id', 'tipo', 'valor', 'motivo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    public function caixaAbertura()
    {
        return $this->belongsTo(PdvCaixaAbertura::class, 'caixa_abertura_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
