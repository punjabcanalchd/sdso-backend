<?php

namespace Modules\Admin\Services;

use App\Models\ImageResizer;
use App\Models\Setting;
use Modules\Admin\Repositories\SettingRepository;
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
        if (isset($data['site_icon']) && $data['site_icon'] instanceof UploadedFile) {
            $fileName = time() . '-' . $data['site_icon']->getClientOriginalName();
            ImageResizer::store($data['site_icon'], 'uploads', $fileName);
            $data['site_icon'] = 'uploads/' . $fileName;
            $data['website_icon'] = 'uploads/' . $fileName;
        } elseif (isset($data['website_icon']) && $data['website_icon'] instanceof UploadedFile) {
            $fileName = time() . '-' . $data['website_icon']->getClientOriginalName();
            ImageResizer::store($data['website_icon'], 'uploads', $fileName);
            $data['site_icon'] = 'uploads/' . $fileName;
            $data['website_icon'] = 'uploads/' . $fileName;
        }

        return $this->repository->updateGeneral($data);
    }

    /**
     * Get Layout Settings
     */
    public function getLayout()
    {
        return $this->repository->getLayout();
    }

    /**
     * Update Layout Settings
     */
    public function updateLayout(array $data)
    {
        $sanitizeName = function (UploadedFile $file): string {
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
            return time() . '-' . $safeBase . '.' . strtolower($extension);
        };

        if (isset($data['default_page_banner']) && $data['default_page_banner'] instanceof UploadedFile) {
            $fileName = $sanitizeName($data['default_page_banner']);
            ImageResizer::store($data['default_page_banner'], 'uploads', $fileName);
            $data['default_page_banner'] = 'uploads/' . $fileName;
            $data['website_default_banner'] = 'uploads/' . $fileName;
        }

        if (isset($data['search_page_banner']) && $data['search_page_banner'] instanceof UploadedFile) {
            $fileName = $sanitizeName($data['search_page_banner']);
            ImageResizer::store($data['search_page_banner'], 'uploads', $fileName);
            $data['search_page_banner'] = 'uploads/' . $fileName;
            $data['website_search_banner'] = 'uploads/' . $fileName;
        }

        if (isset($data['punjab_map']) && $data['punjab_map'] instanceof UploadedFile) {
            $fileName = $sanitizeName($data['punjab_map']);
            ImageResizer::store($data['punjab_map'], 'uploads', $fileName);
            $data['punjab_map'] = 'uploads/' . $fileName;
        }

        return $this->repository->updateLayout($data);
    }

    /**
     * Get Footer Settings
     */
    public function getFooter()
    {
        return $this->repository->getFooter();
    }

    /**
     * Update Footer Settings
     */
    public function updateFooter(array $data)
    {
        return $this->repository->updateFooter($data);
    }

    /**
     * Get Manage Section Settings
     */
    public function getManageSection()
    {
        return $this->repository->getManageSection();
    }

    /**
     * Update Manage Section Settings
     */
    public function updateManageSection(array $data)
    {
        return $this->repository->updateManageSection($data);
    }

    /**
     * Store uploaded image securely adhering to government security policies.
     */
    protected function storeSecureImage(UploadedFile $file, string $path = 'uploads'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new \InvalidArgumentException('Invalid file extension. Only JPG, JPEG, PNG, WEBP are allowed.');
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $fileName = time() . '-' . substr($safeName, 0, 40) . '-' . \Illuminate\Support\Str::random(6) . '.' . $extension;
        ImageResizer::store($file, $path, $fileName);

        return $path . '/' . $fileName;
    }

    /**
     * Get Logo Settings
     */
    public function getLogo()
    {
        return $this->repository->getLogo();
    }

    /**
     * Update Logo Settings
     */
    public function updateLogo(array $data)
    {
        $fileFields = [
            'logo_english'           => 'website_logo',
            'website_logo'           => 'website_logo',
            'logo_punjabi'           => 'website_logo_pb',
            'website_logo_pb'        => 'website_logo_pb',
            'secondary_logo_english' => 'secondary_logo_english',
            'secondary_logo_punjabi' => 'secondary_logo_punjabi',
            'chief_minister_logo'    => 'chief_minister_logo',
            'cabinet_minister_logo'  => 'cabinet_minister_logo',
        ];

        foreach ($fileFields as $key => $dbKey) {
            if (isset($data[$key]) && $data[$key] instanceof UploadedFile) {
                $storedPath = $this->storeSecureImage($data[$key]);
                $data[$key] = $storedPath;
                $data[$dbKey] = $storedPath;
            }
        }

        return $this->repository->updateLogo($data);
    }

    /**
     * Get Contact Us Settings
     */
    public function getContactUs()
    {
        return $this->repository->getContactUs();
    }

    /**
     * Update Contact Us Settings
     */
    public function updateContactUs(array $data)
    {
        return $this->repository->updateContactUs($data);
    }

    /**
     * Get Meta Information Settings
     */
    public function getMetaInformation()
    {
        return $this->repository->getMetaInformation();
    }

    /**
     * Update Meta Information Settings
     */
    public function updateMetaInformation(array $data)
    {
        return $this->repository->updateMetaInformation($data);
    }

    /**
     * Get Additional Settings
     */
    public function getAdditionalSettings()
    {
        return $this->repository->getAdditionalSettings();
    }

    /**
     * Update Additional Settings
     */
    public function updateAdditionalSettings(array $data)
    {
        return $this->repository->updateAdditionalSettings($data);
    }
}