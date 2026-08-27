<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DivisionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use App\Models\Divisions;
use App\Traits\HasPublicId;


class DivisionService
{
    use HasPublicId;
    
    protected DivisionRepository $repository;

    

    public function __construct(DivisionRepository $repository) {
        $this->repository = $repository;
    }

    public function getDivisions(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $divisions = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $divisions->getCollection()->transform(function ($division) {
            return $this->formatResponse($division);
        });

        return $divisions;
    }

    public function getAllDivisions()
    {
        $divisions = $this->repository->getAllDivisions();
        $divisions->transform(function ($division) {
            return $this->formatResponse($division);
        });

        return $divisions;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Division
     * ---------------------------------------------------------------- */

    public function getDivision(string $publicId) {
        $division = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($division);
    }

    /* ------------------------------------------------------------------
     * GET Divisions By Circle
     * ---------------------------------------------------------------- */
    public function getDivisionsByCircle(string $publicId) {
        $circle_id = (int) $this->decode($publicId);
        $divisions = $this->repository->getDivisionsByCircle($circle_id);
        $divisions->transform(function ($division) {
            return $this->formatResponse($division);
        });
        return $divisions;
    }

    private function formatResponse($division)
    {
        $english = $division->description->firstWhere('language_id', 1);
        $punjabi = $division->description->firstWhere('language_id', 2);

        return [
            'public_id' => $division->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'circle'=> $division->circle?->circleDescription?->name,
            'created_at'=> $division->created_at,
            'circle_id' => $division->circle?->public_id,
            'status'    => $division->status,
        ];
    }

    public function createDivision(array $data): Divisions
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
            $publicCircleId = $data['circle_id'];
            if ($publicCircleId) {
                try {
                    $circle_id = (int) $this->decode($publicCircleId);
                } catch (\Exception $e) {
                    $circle_id = 0;
                }
            }
            $data['circle_id'] = $circle_id;
            // Repository handles database operation
            $division = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($division, $translations);

            return $division;
        });
    }

    public function updateDivision(array $data, string $publicId)
    {

        $names = $data['name'] ?? [];
        $description = $data['description'] ?? [];

        unset(
            $data['name'],
            $data['description'],
        );

        $publicCircleId = $data['circle_id'];
        if ($publicCircleId) {
            try {
                $circle_id = (int) $this->decode($publicCircleId);
            } catch (\Exception $e) {
                $circle_id = 0;
            }
        }
        $data['circle_id'] = $circle_id;

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
