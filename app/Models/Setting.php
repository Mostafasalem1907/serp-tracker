<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'is_encrypted', 'group'];

    protected $casts = [
        'is_encrypted' => 'boolean',
        'updated_at'   => 'datetime',
    ];

    /**
     * جلب قيمة إعداد معين — يفك التشفير تلقائياً لو كان مشفراً
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (!$setting || $setting->value === null) {
            return $default;
        }

        if ($setting->is_encrypted) {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Exception) {
                return $default;
            }
        }

        return $setting->value;
    }

    /**
     * حفظ قيمة إعداد — يشفرها تلقائياً لو كانت مشفرة في الـ DB
     */
    public static function setValue(string $key, mixed $value): void
    {
        $setting = static::where('key', $key)->firstOrFail();

        if ($setting->is_encrypted && $value !== null && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        $setting->update(['value' => $value, 'updated_at' => now()]);
    }
}
