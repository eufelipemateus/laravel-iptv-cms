<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IPTVConfig extends Model
{
    use HasFactory;

    /**
     *  Table name
     */
    protected $table = 'iptv_configs';

    /**
     *  Timestamp false.
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Add a settings value
     *
     * @param  string  $type
     * @return bool
     */
    public static function add($key, $val, $type = 'string')
    {
        if (self::has($key)) {
            return self::set($key, $val, $type);
        }

        return self::create(['name' => $key, 'val' => $val, 'type' => $type]) ? $val : false;
    }

    /**
     * Get a settings value
     *
     * @param  null  $default
     * @return bool|int|mixed
     */
    public static function get($key, $default = null)
    {
        if (self::has($key)) {
            $setting = self::all()->where('name', $key)->first();

            return self::castValue($setting->val, $setting->type);
        }

        return self::getDefaultValue($key, $default);
    }

    /**
     * Set a value for setting
     *
     * @param  string  $type
     * @return bool
     */
    public static function set($key, $val, $type = 'string')
    {

        if ($setting = self::all()->where('name', $key)->first()) {

            return $setting->update(
                [
                    'name' => $key,
                    'val' => $val,
                    'type' => $type,
                ]
            ) ? $val : false;
        }

        return self::add($key, $val, $type);
    }

    /**
     * Remove a setting
     *
     * @return bool
     */
    public static function remove($key)
    {
        if (self::has($key)) {
            return self::whereName($key)->delete();
        }

        return false;
    }

    /**
     * Check if setting exists
     *
     * @return bool
     */
    public static function has($key)
    {
        return (bool) self::all()->whereStrict('name', $key)->count();
    }

    /**
     * Get the validation rules for setting fields
     *
     * @return array
     */
    public static function getValidationRules()
    {
        return self::getDefinedSettingFields()->pluck('rules', 'name')
            ->reject(function ($val) {
                return is_null($val);
            })->toArray();
    }

    /**
     * Get the data type of a setting
     *
     * @return mixed
     */
    public static function getDataType($field)
    {
        $type = self::getDefinedSettingFields()
            ->pluck('data', 'name')
            ->get($field);

        return is_null($type) ? 'string' : $type;
    }

    /**
     * Get default value for a setting
     *
     * @return mixed
     */
    public static function getDefaultValueForField($field)
    {
        return self::getDefinedSettingFields()
            ->pluck('value', 'name')
            ->get($field);
    }

    /**
     * Get default value from config if no value passed
     *
     * @return mixed
     */
    private static function getDefaultValue($key, $default)
    {
        return is_null($default) ? self::getDefaultValueForField($key) : $default;
    }

    /**
     * Get all the settings fields from config
     *
     * @return Collection
     */
    private static function getDefinedSettingFields()
    {
        return collect(config('setting_fields'))->pluck('elements')->flatten(1);
    }

    /**
     * caste value into respective type
     *
     * @return bool|int
     */
    private static function castValue($val, $castTo)
    {
        switch ($castTo) {
            case 'int':
            case 'integer':
                return intval($val);
                break;

            case 'bool':
            case 'boolean':
                return filter_var($val, FILTER_VALIDATE_BOOLEAN);
                break;

            default:
                return $val;
        }
    }

    /**
     * Get all boolean the settings
     *
     * @return array
     */
    public static function getAllBoleanSettings()
    {
        return self::where('type', 'bool')->get()->toArray();
    }

    /**
     * Get all string the settings
     *
     * @return array
     */
    public static function getAllStringSettings()
    {
        return self::where('type', 'string')->get()->toArray();
    }
}
