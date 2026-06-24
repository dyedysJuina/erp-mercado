<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function index()
    {
        return view('setup');
    }

    public function run()
    {
        $output = '';

        $output .= "=== INICIANDO SETUP ===\n\n";

        // Rodar migrations
        $output .= "--- Rodando migrations ---\n";
        Artisan::call('migrate', ['--force' => true]);
        $output .= Artisan::output() . "\n";

        // Rodar seeders
        $output .= "--- Rodando seeders ---\n";
        Artisan::call('db:seed', ['--force' => true]);
        $output .= Artisan::output() . "\n";

        // Storage link
        $output .= "--- Storage link ---\n";
        if (!file_exists(public_path('storage'))) {
            Artisan::call('storage:link');
            $output .= Artisan::output() . "\n";
        } else {
            $output .= "Storage link ja existe.\n";
        }

        // Config cache
        $output .= "--- Otimizando ---\n";
        Artisan::call('optimize');
        $output .= Artisan::output() . "\n";

        $output .= "=== SETUP CONCLUIDO COM SUCESSO! ===\n";

        // Salvar log
        file_put_contents(storage_path('logs/setup-' . date('YmdHis') . '.log'), $output);

        return response("<pre>{$output}</pre>");
    }
}
