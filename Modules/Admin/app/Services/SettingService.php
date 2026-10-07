<?php

namespace App\Services;

use App\Models\ImageResizer;
use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    protected SettingRepository $repository;

    public function __construct(SettingRepository $repository)
    {
        $this->repository = $repository;
    }

     /**
     * Get General Settings
     */
    public function getGeneral()
    {
        return $this->repository->getGeneral();
    }

    /**
     * Update General Settings
     */
    public function updateGeneral(array $data)
    {
        return $this->repository->updateGeneral($data);
    }





    // /**
    //  * Get all settings.
    //  */
    // public function getAll(): array
    // {
    //     $settings = $this->repository->all();

    //     $data = [];

    //     foreach ($settings as $setting) {

    //         $value = $setting->config_value;

    //         if (
    //             in_array(
    //                 $setting->config_key,
    //                 Setting::SERIALIZED_FIELDS,
    //                 true
    //             )
    //         ) {
    //             $value = @unserialize($value);

    //             if ($value === false && $value !== 'b:0;') {
    //                 $value = [];
    //             }
    //         }

    //         $data[$setting->config_key] = $value;
    //     }

    //     return $data;
    // }

    // /**
    //  * Get one setting.
    //  */
    // public function get(string $key, mixed $default = null): mixed
    // {
    //     $setting = $this->repository->findByKey($key);

    //     if (!$setting) {
    //         return $default;
    //     }

    //     if (
    //         in_array(
    //             $key,
    //             Setting::SERIALIZED_FIELDS,
    //             true
    //         )
    //     ) {
    //         return unserialize($setting->config_value);
    //     }

    //     return $setting->config_value;
    // }

    // /**
    //  * Update settings.
    //  */
    // public function update(array $data): array
    // {
    //     return DB::transaction(function () use ($data) {

    //         foreach ($data as $key => $value) {

    //             if ($value instanceof UploadedFile) {
    //                 $value = $this->uploadFile($key, $value);
    //             }

    //             if (
    //                 in_array(
    //                     $key,
    //                     Setting::SERIALIZED_FIELDS,
    //                     true
    //                 )
    //             ) {
    //                 $value = serialize($value);
    //             }

    //             $this->repository->updateOrCreate(
    //                 $key,
    //                 $value
    //             );
    //         }

    //         return $this->getAll();
    //     });
    // }

    // /**
    //  * Upload setting file.
    //  */
    // protected function uploadFile(
    //     string $key,
    //     UploadedFile $file
    // ): string {

    //     $fileName = time() . '-' . $file->getClientOriginalName();

    //     return ImageResizer::store(
    //         $file,
    //         'uploads',
    //         $fileName
    //     );
    // }

    // /**
    //  * Delete setting.
    //  */
    // public function delete(string $key): bool
    // {
    //     return $this->repository->delete($key);
    // }
}