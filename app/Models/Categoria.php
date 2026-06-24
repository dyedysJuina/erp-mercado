<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Categoria extends Model
{
    protected $table = 'categorias';

    public $timestamps = false;

    protected $fillable = [
        'parent_id', 'categoria_raiz_id', 'nome', 'slug',
        'caminho', 'nivel', 'icone', 'ordem',
        'permite_produtos', 'destaque_site', 'ativo',
    ];

    protected function casts(): array
    {
        return [
            'permite_produtos' => 'boolean',
            'destaque_site' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('ordem')->orderBy('nome');
    }

    public function categoriaRaiz()
    {
        return $this->belongsTo(self::class, 'categoria_raiz_id');
    }

    public function isAncestorOf(self $other): bool
    {
        if (!$other->parent_id) return false;
        if ($other->parent_id === $this->id) return true;
        return $other->parent && $this->isAncestorOf($other->parent);
    }

    public static function gerarSlugUnico(string $nome, ?int $ignorarId = null): string
    {
        $slug = Str::slug($nome);
        $original = $slug;
        $contador = 1;
        while (self::where('slug', $slug)->when($ignorarId, fn($q) => $q->where('id', '!=', $ignorarId))->exists()) {
            $slug = $original . '-' . $contador++;
        }
        return $slug;
    }

    public static function recalcularCaminho(self $categoria): void
    {
        $partes = [];
        $atual = $categoria;
        $partes[] = $atual->nome;
        while ($atual->parent) {
            $atual = $atual->parent;
            $partes[] = $atual->nome;
        }
        $caminho = implode(' > ', array_reverse($partes));
        $nivel = count($partes);

        self::withoutTimestamps(fn() => self::where('id', $categoria->id)->update([
            'caminho' => $caminho,
            'nivel' => $nivel,
        ]));
    }

    public static function recalcularDescendentes(int $categoriaId): void
    {
        $filhos = self::where('parent_id', $categoriaId)->get();
        foreach ($filhos as $filho) {
            $pai = $filho->parent;
            $caminho = $pai ? ($pai->caminho . ' > ' . $filho->nome) : $filho->nome;
            $nivel = $pai ? ($pai->nivel + 1) : 1;

            self::withoutTimestamps(fn() => self::where('id', $filho->id)->update([
                'caminho' => $caminho,
                'nivel' => $nivel,
            ]));

            self::recalcularDescendentes($filho->id);
        }
    }
}
