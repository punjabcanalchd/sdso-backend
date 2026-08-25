<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\CircleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Circles;


class CircleService
{
    protected CircleRepository $repository;

    public function __construct(CircleRepository $repository) {
        $this->repository = $repository;
    }

    public function getCircles(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $circles = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $circles->getCollection()->transform(function ($circle) {
            return $this->formatResponse($circle);
        });

        return $circles;
    }

    public function getAllCircles()
    {
        $circles = $this->repository->getAllCircles();
        $circles->transform(function ($circle) {
            return $this->formatResponse($circle);
        });

        return $circles;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function getCircle(string $publicId) {
        $circle = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($circle);
    }

    private function formatResponse($circle)
    {
        $english = $circle->description->firstWhere('language_id', 1);
        $punjabi = $circle->description->firstWhere('language_id', 2);

        return [
            'public_id' => $circle->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'state'=> $circle->state?->stateDescription?->name,
            'lgdstatecode'=> $circle->lgdstatecode,
            'created_at'=> $circle->created_at,
            'status'    => $circle->status,
        ];
    }

    public function createCircle(array $data): Circles
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
            $circle = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($circle, $translations);

            return $circle;
        });
    }

    public function updateCircle(array $data, string $publicId)
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
