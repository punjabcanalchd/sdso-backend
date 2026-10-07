<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository
{
    protected Setting $model;

    public function __construct(Setting $model)
    {
        $this->model = $model;
    }

    /**
     * Get all settings.
     */
    public function all(): Collection
    {
        return $this->model
            ->orderBy('setting_id')
            ->get();
    }

    /**
     * Get settings as key/value array.
     */
    public function getAllAsArray(): array
    {
        return $this->all()
            ->mapWithKeys(function ($setting) {
                return [
                    $setting->config_key => $setting->config_value,
                ];
            })
            ->toArray();
    }

    /**
     * Find setting by key.
     */
    public function findByKey(string $key): ?Setting
    {
        return $this->model
            ->where('config_key', $key)
            ->first();
    }

    /**
     * Get value by key.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        $setting = $this->findByKey($key);

        return $setting?->config_value ?? $default;
    }

    /**
     * Create or update setting.
     */
    public function updateOrCreate(string $key, mixed $value): Setting
    {
        return $this->model->updateOrCreate(
            [
                'config_key' => $key,
            ],
            [
                'config_value' => $value,
            ]
        );
    }

    /**
     * Delete setting.
     */
    public function delete(string $key): bool
    {
        return $this->model
            ->where('config_key', $key)
            ->delete() > 0;
    }
}