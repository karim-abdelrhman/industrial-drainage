<?php

namespace App\Support;

use App\Models\Sample;
use Illuminate\Support\Facades\DB;

final class SampleNumber
{
    public static function next(): string
    {
        return DB::transaction(function (): string {
            Sample::query()->lockForUpdate()->orderByDesc('id')->first();

            $max = Sample::query()
                ->where('sample_number', 'like', 'SMP-%')
                ->pluck('sample_number')
                ->reduce(function (int $highest, string $number): int {
                    if (! preg_match('/^SMP-(\d+)$/', $number, $matches)) {
                        return $highest;
                    }

                    return max($highest, (int) $matches[1]);
                }, 0);

            return 'SMP-'.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
        });
    }
}
