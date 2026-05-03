<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
    ];

    public static function get(string $key, float $default = 0.0): float
    {
        $setting = static::find($key);

        return $setting ? (float) $setting->value : $default;
    }
}
