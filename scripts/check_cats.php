<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Categorias atuais: " . DB::table('categorias')->count() . "\n";
$cats = DB::table('categorias')->get(['id','nome','slug','nivel','parent_id']);
foreach ($cats as $c) {
    $pai = $c->parent_id ? DB::table('categorias')->where('id',$c->parent_id)->value('nome') : '—';
    echo "#{$c->id} {$c->nome} (slug:{$c->slug}) N{$c->nivel} pai:{$pai}\n";
}
