<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::find(1);
if ($user) {
    $user->assignRole('Admin');
    echo "✅ Admin role assigned to user {$user->id}: {$user->email}\n";
} else {
    echo "User not found\n";
}
