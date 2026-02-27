<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $s = static::find($key);
            return $s ? $s->value : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    public static function getAcademicYear(): string
    {
        $stored = static::get('academic_year');
        if ($stored) {
            return $stored;
        }
        $year = (int) date('Y');
        $month = (int) date('n');
        return $month >= 7 ? "{$year}/" . ($year + 1) : ($year - 1) . "/{$year}";
    }

    public static function setAcademicYear(string $value): void
    {
        static::set('academic_year', $value);
    }

    public static function nextAcademicYear(string $current): string
    {
        $parts = explode('/', $current);
        if (count($parts) === 2) {
            $a = (int) trim($parts[0]);
            $b = (int) trim($parts[1]);
            return "{$b}/" . ($b + 1);
        }
        return $current;
    }
}
