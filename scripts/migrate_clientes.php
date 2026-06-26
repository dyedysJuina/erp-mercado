<?php
$base = 'C:\wamp64\www\erp_mercado';
require $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$clientes = App\Models\Cliente::whereNull('user_id')->whereNotNull('email')->get();
foreach ($clientes as $c) {
    $u = App\Models\User::where('email', $c->email)->first();
    if (!$u) {
        $u = App\Models\User::create([
            'name' => $c->nome,
            'email' => $c->email,
            'password' => $c->password ?? \Illuminate\Support\Str::random(16),
            'whatsapp' => $c->whatsapp,
        ]);
    }
    $c->user_id = $u->id;
    $c->save();
    echo "OK: Cliente #{$c->id} -> User #{$u->id}\n";
}
echo "DONE\n";
