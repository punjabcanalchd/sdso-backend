<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\CircleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


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
            'state'=> $circle->state?->stateDescription?->name,
            'created_at'=> $circle->created_at,
            'status'    => $circle->status,
        ];
    }
}
