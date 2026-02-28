<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private string $cacheKey = 'app.settings.all';

    public function all(): array
    {
        return Cache::rememberForever($this->cacheKey, function () {
            $rows = Setting::query()->get(['key', 'value', 'type']);
            $out = [];

            foreach ($rows as $row) {
                $out[$row->key] = $this->castOut($row->value, $row->type);
            }

            return $out;
        });
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();
        return $all[$key] ?? $default;
    }

    public function set(string $key, $value, string $type = 'string'): void
    {
        $row = Setting::query()->firstOrNew(['key' => $key]);
        $row->type = $type;
        $row->value = $this->castIn($value, $type);
        $row->save();

        $this->clearCache();
    }

    public function setMany(array $items): void
    {
        foreach ($items as $item) {
            $this->set($item['key'], $item['value'], $item['type'] ?? 'string');
        }
    }

    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    private function castIn($value, string $type): array
    {
        return match ($type) {
            'bool' => ['v' => (bool) $value],
            'int'  => ['v' => (int) $value],
            'json' => ['v' => is_array($value) ? $value : (array) $value],
            'text' => ['v' => (string) $value],
            default => ['v' => (string) $value],
        };
    }

    private function castOut($value, string $type)
    {
        // $value هو array بسبب casts
        $v = is_array($value) ? ($value['v'] ?? null) : null;

        return match ($type) {
            'bool' => (bool) $v,
            'int'  => (int) $v,
            'json' => $v ?? [],
            'text' => (string) ($v ?? ''),
            default => (string) ($v ?? ''),
        };
    }
}