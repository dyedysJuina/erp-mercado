<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Ncm model check:\n";
try {
    $ncm = new App\Models\Ncm();
    echo "  table: " . $ncm->getTable() . "\n";
    echo "  timestamps: " . ($ncm->usesTimestamps() ? 'YES' : 'NO') . "\n";
} catch (\Exception $e) {
    echo "  ERROR: " . $e->getMessage() . "\n";
}

echo "\nDirect find:\n";
$n = App\Models\Ncm::find(248);
echo "  id=248: " . ($n ? $n->codigo . ' - ' . $n->descricao : 'NULL') . "\n";

echo "\nVariacao ncm_id check:\n";
$v = App\Models\ProdutoVariacao::with('ncm')->find(4);
echo "  variacao #4 ncm_id={$v->ncm_id}\n";
echo "  ncm relation: " . ($v->ncm ? $v->ncm->codigo : 'NULL') . "\n";
echo "  relation loaded: " . ($v->relationLoaded('ncm') ? 'YES' : 'NO') . "\n";

// Force reload
$v->unsetRelation('ncm');
$v->load('ncm');
echo "  after unset+load: " . ($v->ncm ? $v->ncm->codigo : 'NULL') . "\n";
