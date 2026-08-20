<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DesignationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Designation;


class DesignationService
{
    protected DesignationRepository $repository;

    public function __construct(DesignationRepository $repository) {
        $this->repository = $repository;
    }

    public function getDesignations(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $designations = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $designations->getCollection()->transform(function ($designation) {
            return $this->formatResponse($designation);
        });

        return $designations;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Designation
     * ---------------------------------------------------------------- */

    public function getDesignation(string $publicId) {
        $designation = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($designation);
    }

    private function formatResponse($designation)
    {
        $english = $designation->description->firstWhere('language_id', 1);
        $punjabi = $designation->description->firstWhere('language_id', 2);

        return [
            'public_id' => $designation->public_id,
            'name_en'   => $english?->designation,
            'name_pb'   => $punjabi?->designation,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'desigsenioritylevel'=> $designation->desigsenioritylevel,
            'created_at'=> $designation->created_at,
            'status'    => $designation->status,
        ];
    }

        public function createDesignation(array $data): Designation
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
            $officeHierarchy = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($officeHierarchy, $translations);

            return $officeHierarchy;
        });
    }

    public function updateDesignation(array $data, string $publicId)
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
