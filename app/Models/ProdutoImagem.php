<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProdutoImagem extends Model
{
    protected $table = 'produto_imagens';

    protected $fillable = [
        'produto_variacao_id', 'url', 'ordem', 'principal', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
            'principal' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function variacao()
    {
        return $this->belongsTo(ProdutoVariacao::class, 'produto_variacao_id');
    }

    public function url(): string
    {
        if (str_starts_with($this->url, 'http')) {
            return $this->url;
        }
        return Storage::url($this->url);
    }
}
