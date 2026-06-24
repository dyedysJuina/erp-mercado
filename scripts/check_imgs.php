<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$imgs = DB::table('produto_imagens')->where('produto_variacao_id', 4)->first();
if ($imgs) {
    $url = $imgs->url;
    echo "URL: $url\n";
    $full = storage_path('app/public/' . $url);
    echo "Full path: $full\n";
    echo "Exists: " . (file_exists($full) ? 'YES' : 'NO') . "\n";
} else {
    echo "No images found for variação #4\n";
}

$count = DB::table('produto_imagens')->count();
echo "\nTotal images: $count\n";

// Check storage link
echo "\nPublic/storage: " . (is_link(public_path('storage')) ? 'SYMLINK' : 'NOT LINK') . "\n";
