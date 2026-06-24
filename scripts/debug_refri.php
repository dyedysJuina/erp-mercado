<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$v = App\Models\ProdutoVariacao::where('sku', 'REF001')->first();
echo "ncm_id = {$v->ncm_id}\n";

// Direct lookup
$ncm = DB::table('ncm')->where('id', $v->ncm_id)->first();
echo "Direto: " . ($ncm ? $ncm->codigo : 'NULL') . "\n";

// Via Ncm model
$ncmModel = App\Models\Ncm::find($v->ncm_id);
echo "Model find: " . ($ncmModel ? $ncmModel->codigo : 'NULL') . "\n";

// Load and check
$v->load('ncm');
echo "After load: " . ($v->ncm ? $v->ncm->codigo : 'NULL') . "\n";
echo "Relation loaded: " . ($v->relationLoaded('ncm') ? 'YES' : 'NO') . "\n";
