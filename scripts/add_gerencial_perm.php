<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

$perm = Permission::firstOrCreate(['name' => 'admin.gerencial', 'guard_name' => 'web']);
echo "Permission created: {$perm->name}\n";

$admin = Role::findByName('Admin');
$admin->givePermissionTo('admin.gerencial');
echo "Permission assigned to Admin.\n";
