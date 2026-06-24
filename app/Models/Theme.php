<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = ['slug', 'name', 'is_active', 'tokens'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'tokens' => 'array',
        ];
    }

    public function toCssVars(): string
    {
        $css = '';
        foreach ($this->tokens as $key => $value) {
            $var = str_replace('_', '-', $key);
            $css .= "    --{$var}: {$value};\n";
        }
        return $css;
    }
}
