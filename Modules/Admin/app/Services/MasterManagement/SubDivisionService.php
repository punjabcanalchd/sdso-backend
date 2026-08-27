<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\SubDivisionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\SubDivisions;
use App\Traits\HasPublicId;


class SubDivisionService
{
    use HasPublicId;
    protected SubDivisionRepository $repository;

    public function __construct(SubDivisionRepository $repository) {
        $this->repository = $repository;
    }

    public function getSubDivisions(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $subdivisions = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $subdivisions->getCollection()->transform(function ($subdivision) {
            return $this->formatResponse($subdivision);
        });

        return $subdivisions;
    }

    public function getAllSubDivisions()
    {
        $subdivisions = $this->repository->getAllSubDivisions();
        $subdivisions->transform(function ($subdivision) {
            return $this->formatResponse($subdivision);
        });

        return $subdivisions;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE SubDivision
     * ---------------------------------------------------------------- */

    public function getSubDivision(string $publicId) {
        $subdivision = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($subdivision);
    }

    /* ------------------------------------------------------------------
     * GET SubDivisions By Division
     * ---------------------------------------------------------------- */
    public function getSubdivisionsByDivision(string $publicId) {
        $division_id = (int) $this->decode($publicId);
        $subdivisions = $this->repository->getSubdivisionsByDivision($division_id);
        $subdivisions->transform(function ($subdivision) {
            return $this->formatResponse($subdivision);
        });
        return $subdivisions;
    }

    private function formatResponse($subdivision)
    {
        $english = $subdivision->description->firstWhere('language_id', 1);
        $punjabi = $subdivision->description->firstWhere('language_id', 2);

        return [
            'public_id' => $subdivision->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'division'=> $subdivision->division?->divisionDescription?->name,
            'created_at'=> $subdivision->created_at,
            'division_id'=> $subdivision->division?->public_id,
            'status'    => $subdivision->status,
        ];
    }

    public function createSubDivision(array $data): SubDivisions
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
            $publicDivisionId = $data['division_id'];
            if ($publicDivisionId) {
                try {
                    $division_id = (int) $this->decode($publicDivisionId);
                } catch (\Exception $e) {
                    $division_id = 0;
                }
            }
            $data['division_id'] = $division_id;
            // Repository handles database operation
            $division = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($division, $translations);

            return $division;
        });
    }

    public function updateSubDivision(array $data, string $publicId)
    {

        $names = $data['name'] ?? [];
        $description = $data['description'] ?? [];

        unset(
            $data['name'],
            $data['description'],
        );

        $publicDivisionId = $data['division_id'];
        if ($publicDivisionId) {
            try {
                $division_id = (int) $this->decode($publicDivisionId);
            } catch (\Exception $e) {
                $division_id = 0;
            }
        }
        $data['division_id'] = $division_id;

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
