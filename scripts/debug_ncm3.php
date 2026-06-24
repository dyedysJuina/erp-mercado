<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

$v = App\Models\ProdutoVariacao::find(4);
echo "ncm_id={$v->ncm_id}\n";

// Get the relationship query
$rel = $v->ncm();
echo "relation class: " . get_class($rel) . "\n";

// Get the relationship query SQL
$sql = $rel->toSql();
$bindings = $rel->getBindings();
echo "SQL: $sql\n";
echo "Bindings: " . json_encode($bindings) . "\n";

// Try direct query
$result = $rel->first();
echo "first(): " . ($result ? $result->codigo : 'NULL') . "\n";

// Manual query
$manual = App\Models\Ncm::where('id', $v->ncm_id)->first();
echo "manual: " . ($manual ? $manual->codigo : 'NULL') . "\n";
