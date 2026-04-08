<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'description'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value, ?string $group = null, ?string $description = null): void
    {
        static::updateOrCreate(['key' => $key], [
            'value' => $value,
            'group' => $group ?? 'general',
            'description' => $description,
        ]);
        Cache::forget("setting:{$key}");
    }
}
