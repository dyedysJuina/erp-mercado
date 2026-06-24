<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'usuario_id', 'cliente_id', 'canal',
        'titulo', 'mensagem', 'link', 'status',
        'enviada_at', 'lida_at',
    ];

    protected function casts(): array
    {
        return [
            'enviada_at' => 'datetime',
            'lida_at' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
