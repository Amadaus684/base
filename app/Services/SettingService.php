<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    public function isConfigured(): bool
    {
        $requiredSettings = [
            'village.code',
            'letter_number.number_padding',
            'letter_number.format_components',
            'letter_number.reset_period',
            'letter_number.numbering_scope',
            'localization.date_format',
            'api.base_url',
            'api.token',
            'site.name',
        ];

        foreach ($requiredSettings as $key) {
            $value = $this->get($key);

            if ($value === null || $value === '' || $value === []) {
                return false;
            }
        }

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'encrypted') {
            try {
                return Crypt::decryptString($setting->value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $setting->typed_value;
    }

    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general'
    ): Setting {
        $storedValue = match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'float' => (string) $value,
            'json', 'array' => json_encode($value),
            'encrypted' => Crypt::encryptString((string) $value),
            default => (string) $value,
        };

        return Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group,
            ]
        );
    }

    public function forget(string $key): bool
    {
        return Setting::where('key', $key)->delete() > 0;
    }
}