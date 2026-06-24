<?php

namespace App\Support;

use InvalidArgumentException;

final class BrazilianNumber
{
    public static function decimal(mixed $value, int $scale = 2): string
    {
        if ($value === null || $value === '') {
            return number_format(0, $scale, '.', '');
        }

        if (is_int($value) || is_float($value)) {
            return number_format(round((float) $value, $scale, PHP_ROUND_HALF_UP), $scale, '.', '');
        }

        $normalized = trim((string) $value);
        $normalized = str_ireplace(['R$', '%', "\u{00A0}", ' '], '', $normalized);

        if (str_contains($normalized, ',')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        }

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $normalized)) {
            throw new InvalidArgumentException('Valor numérico inválido.');
        }

        return number_format(
            round((float) $normalized, $scale, PHP_ROUND_HALF_UP),
            $scale,
            '.',
            ''
        );
    }

    public static function currency(mixed $value): string
    {
        return 'R$ '.number_format((float) self::decimal($value), 2, ',', '.');
    }

    public static function validarCpf(?string $cpf): bool
    {
        if (!$cpf) return false;
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }
}
