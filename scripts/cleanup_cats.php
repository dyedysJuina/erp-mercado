<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// IDs das categorias obsoletas para inativar
$ids = [1, 8, 9, 10, 11, 12];

foreach ($ids as $id) {
    $cat = App\Models\Categoria::find($id);
    if ($cat) {
        $cat->update(['ativo' => false]);
        echo "✕ Inativada: {$cat->nome} (#{$cat->id})\n";
    }
}

echo "\n✅ Categorias obsoletas inativadas!\n";

// Verifica os N1 ativos
echo "\n=== N1 Ativos ===\n";
$n1s = App\Models\Categoria::whereNull('parent_id')->where('ativo', true)->orderBy('nome')->get(['id','nome','slug']);
foreach ($n1s as $n) echo "{$n->id}: {$n->nome}\n";
