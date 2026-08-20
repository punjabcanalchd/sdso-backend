<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DistrictRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Districts;


class DistrictService
{
    protected DistrictRepository $repository;

    public function __construct(DistrictRepository $repository) {
        $this->repository = $repository;
    }

    public function getDistricts(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $districts = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $districts->getCollection()->transform(function ($district) {
            return $this->formatResponse($district);
        });

        return $districts;
    }

    public function getAllDistricts()
    {
        $districts = $this->repository->getAllDistricts();
        $districts->transform(function ($district) {
            return $this->formatResponse($district);
        });

        return $districts;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE District
     * ---------------------------------------------------------------- */

    public function getDistrict(string $publicId) {
        $district = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($district);
    }

    private function formatResponse($district)
    {
        $english = $district->description->firstWhere('language_id', 1);
        $punjabi = $district->description->firstWhere('language_id', 2);

        return [
            'public_id' => $district->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'state'=> $district->state?->stateDescription?->name,
            'created_at'=> $district->created_at,
            'lgdstatecode'=> $district->lgdstatecode,
            'lgddistcode'=> $district->lgddistcode,
            'status'    => $district->status,
        ];
    }

    public function createDistrict(array $data): Districts
    {
        return DB::transaction(function () use ($data) {

            $translations = [
                'name' => $data['name'] ?? [],
                'description' => $data['description'] ?? [],
            ];

            unset(
                $data['name'],
                $data['description'],
            );

            // Repository handles database operation
            $district = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($district, $translations);

            return $district;
        });
    }

    public function updateDistrict(array $data, string $publicId)
    {

        $names = $data['name'] ?? [];
        $description = $data['description'] ?? [];

        unset(
            $data['name'],
            $data['description'],
        );

        $descriptions = [];

        foreach ($names as $languageId => $name) {

            $descriptions[] = [
                'language_id' => $languageId,
                'name' => $name,
                'description' => $description[$languageId] ?? null,
            ];
        }

        DB::transaction(function () use ($publicId, $data, $descriptions) {

            return $this->repository->updatePageWithDescriptions(
                $publicId,
                $data,
                $descriptions
            );
        });

    }
}
