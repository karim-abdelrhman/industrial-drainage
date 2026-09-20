<?php

namespace App\Support;

final class Money
{
    public static function format(float|int|string|null $amount, int $decimals = 2): string
    {
        if ($amount === null || $amount === '') {
            return '—';
        }

        return number_format((float) $amount, $decimals).' ج.م';
    }
}
