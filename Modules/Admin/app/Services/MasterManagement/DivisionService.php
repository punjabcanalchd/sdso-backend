<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DivisionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DivisionService
{
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

    /* ------------------------------------------------------------------
     * GET SINGLE Division
     * ---------------------------------------------------------------- */

    public function getDivision(string $publicId) {
        $division = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($division);
    }

    private function formatResponse($division)
    {
        $english = $division->description->firstWhere('language_id', 1);
        $punjabi = $division->description->firstWhere('language_id', 2);

        return [
            'public_id' => $division->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'circle'=> $division->circle?->circleDescription?->name,
            'created_at'=> $division->created_at,
            'status'    => $division->status,
        ];
    }
}
