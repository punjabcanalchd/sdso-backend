<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Yungts97\LaravelUserActivityLog\Traits\Loggable;

/**
 * Setting
 *
 * @mixin Builder
 */
class Setting extends Model
{
    use HasFactory;

    protected $table = 'settings';

    protected $primaryKey = 'setting_id';

    public $timestamps = false;

    protected $fillable = [
        'config_key',
        'config_value',
    ];

    /**
     * Fields which contain serialized arrays.
     */
    public const SERIALIZED_FIELDS = [
        'footer_cat1_menus',
        'footer_cat2_menus',
        'footer_cat3_menus',
        'website_menus',
    ];

    /**
     * Get setting value.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('config_key', $key)->first();

        if (!$setting) {
            return $default;
        }

        if (in_array($key, self::SERIALIZED_FIELDS, true)) {
            return unserialize($setting->config_value) ?: $default;
        }

        return $setting->config_value;
    }

    /**
     * Set setting value.
     */
    public static function setValue(string $key, mixed $value): self
    {
        if (in_array($key, self::SERIALIZED_FIELDS, true)) {
            $value = serialize($value);
        }

        return static::updateOrCreate(
            [
                'config_key' => $key,
            ],
            [
                'config_value' => $value,
            ]
        );
    }

    public static function MobilePagination()
    {
        return 10;
    }
}