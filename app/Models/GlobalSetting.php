<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class GlobalSetting extends Model
{
    use HasFactory, AsSource;

    protected $table = 'global_settings';

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected $casts = [
        // Adicione casts se necessário (ex: 'value' => 'array' para settings tipo JSON)
    ];

    /**
     * Retorna o valor de uma chave, se existir.
     */
    public static function getValue(string $key, $default = null): mixed
    {
        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        // Simples decodificação baseada no tipo (expansível)
        return match ($setting->type) {
            'json'    => json_decode($setting->value, true),
            'boolean' => (bool) $setting->value,
            'int'     => (int) $setting->value,
            default   => $setting->value,
        };
    }
}