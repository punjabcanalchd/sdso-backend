<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\SubDivisionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class SubDivisionService
{
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

    /* ------------------------------------------------------------------
     * GET SINGLE SubDivision
     * ---------------------------------------------------------------- */

    public function getSubDivision(string $publicId) {
        $subdivision = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($subdivision);
    }

    private function formatResponse($subdivision)
    {
        $english = $subdivision->description->firstWhere('language_id', 1);
        $punjabi = $subdivision->description->firstWhere('language_id', 2);

        return [
            'public_id' => $subdivision->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'division'=> $subdivision->division?->divisionDescription?->name,
            'created_at'=> $subdivision->created_at,
            'status'    => $subdivision->status,
        ];
    }
}
