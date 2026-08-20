<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\OfficeHierarchyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\OfficeHierarchies;



class OfficeHierarchyService
{
    protected OfficeHierarchyRepository $repository;

    public function __construct(OfficeHierarchyRepository $repository) {
        $this->repository = $repository;
    }

    public function getOfficeHierarchies(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $officeHierarchies = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $officeHierarchies->getCollection()->transform(function ($officeHierarchy) {
            return $this->formatResponse($officeHierarchy);
        });

        return $officeHierarchies;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function getOfficeHierarchy(string $publicId) {
        $officeHierarchy = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($officeHierarchy);
    }

    private function formatResponse($officeHierarchy)
    {
        $english = $officeHierarchy->description->firstWhere('language_id', 1);
        $punjabi = $officeHierarchy->description->firstWhere('language_id', 2);

        return [
            'public_id' => $officeHierarchy->public_id,
            'name_en'   => $english?->officelevel,
            'name_pb'   => $punjabi?->officelevel,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'officesenioritylevel'=> $officeHierarchy->officesenioritylevel,
            'created_at'=> $officeHierarchy->created_at,
            'status'    => $officeHierarchy->status,
        ];
    }

    public function createOfficeHierarchy(array $data): OfficeHierarchies
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

    public function updateOfficeHierarchy(array $data, string $publicId)
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
