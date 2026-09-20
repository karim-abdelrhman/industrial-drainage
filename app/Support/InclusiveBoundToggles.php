<?php

namespace App\Support;

use Filament\Forms\Components\Toggle;

final class InclusiveBoundToggles
{
    public static function lower(string $name): Toggle
    {
        return Toggle::make($name)
            ->label('هل الرقم الأدنى داخل النطاق؟')
            ->helperText('مفتوح: القيمة تساوي الحد الأدنى تدخل («من 660»). مقفول: لازم تكون أكبر منه («أكبر من 244»).')
            ->inline(false);
    }

    public static function upper(string $name): Toggle
    {
        return Toggle::make($name)
            ->label('هل الرقم الأقصى داخل النطاق؟')
            ->helperText('مفتوح: القيمة تساوي الحد الأقصى تدخل («حتى 800»). مقفول: لازم تكون أقل منه («أقل من 660»).')
            ->inline(false);
    }

    public static function formatLower(float|string|null $value, bool $inclusive): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return ($inclusive ? 'من ' : 'أكبر من ').self::number($value);
    }

    public static function formatUpper(float|string|null $value, bool $inclusive): string
    {
        if ($value === null || $value === '') {
            return 'مفتوح';
        }

        return ($inclusive ? 'حتى ' : 'أقل من ').self::number($value);
    }

    private static function number(float|string $value): string
    {
        $number = (float) $value;

        return fmod($number, 1.0) === 0.0
            ? number_format($number, 0)
            : rtrim(rtrim(number_format($number, 4, '.', ''), '0'), '.');
    }
}
