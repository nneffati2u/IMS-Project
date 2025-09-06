<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Setting extends Model
{
    public $timestamps = false;
    protected $fillable = ['key', 'value'];
    public static function get(string $key, $default = null)
    {
        $f = static::where('key', $key)->first();
        return $f ? $f->value : $default;
    }
    public static function set(string $key, $value)
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
