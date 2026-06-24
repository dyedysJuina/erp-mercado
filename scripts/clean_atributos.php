<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Atributo;
use Illuminate\Support\Facades\DB;

echo "Limpando vínculos...\n";
DB::table('categorias_atributos')->delete();
echo "Vínculos removidos.\n";

echo "Limpando atributos...\n";
Atributo::query()->delete();
echo "Atributos removidos.\n";

echo "Pronto!\n";
