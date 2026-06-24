<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Atributo;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;

echo "=== Atributos por Departamento ===\n\n";
$cats = Categoria::whereNull('parent_id')->where('ativo', true)->orderBy('nome')->get();
foreach ($cats as $cat) {
    $vinculos = DB::table('categorias_atributos')
        ->join('atributos', 'atributos.id', '=', 'categorias_atributos.atributo_id')
        ->where('categorias_atributos.categoria_id', $cat->id)
        ->orderBy('categorias_atributos.ordem')
        ->get(['atributos.nome', 'atributos.tipo', 'atributos.opcoes']);
    echo "{$cat->nome} ({$vinculos->count()} atributos)\n";
    foreach ($vinculos as $v) {
        $opc = $v->opcoes ? ' [' . implode(', ', json_decode($v->opcoes, true) ?? []) . ']' : '';
        echo "  - {$v->nome} ({$v->tipo}){$opc}\n";
    }
    echo "\n";
}

echo "=== Resumo ===\n";
echo "Atributos: " . Atributo::count() . "\n";
echo "Vínculos: " . DB::table('categorias_atributos')->count() . "\n";
echo "Departamentos vinculados: " . DB::table('categorias_atributos')->distinct('categoria_id')->count('categoria_id') . "\n";
