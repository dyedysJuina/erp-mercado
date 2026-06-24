<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $defaults = [
        'on_primary' => '#ffffff',
        'on_success' => '#ffffff',
        'on_warning' => '#111827',
        'on_danger' => '#ffffff',
        'shadow' => '#0f172a',
    ];

    public function up(): void
    {
        DB::table('themes')->orderBy('id')->each(function (object $theme): void {
            $tokens = json_decode($theme->tokens, true, flags: JSON_THROW_ON_ERROR);

            DB::table('themes')->where('id', $theme->id)->update([
                'tokens' => json_encode(array_merge($this->defaults, $tokens), JSON_THROW_ON_ERROR),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('themes')->orderBy('id')->each(function (object $theme): void {
            $tokens = json_decode($theme->tokens, true, flags: JSON_THROW_ON_ERROR);

            foreach (array_keys($this->defaults) as $key) {
                unset($tokens[$key]);
            }

            DB::table('themes')->where('id', $theme->id)->update([
                'tokens' => json_encode($tokens, JSON_THROW_ON_ERROR),
            ]);
        });
    }
};
