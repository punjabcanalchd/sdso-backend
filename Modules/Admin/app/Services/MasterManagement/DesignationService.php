<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DesignationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


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
     * GET SINGLE Office Hierarchy
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
            'desigsenioritylevel'=> $designation->desigsenioritylevel,
            'created_at'=> $designation->created_at,
            'status'    => $designation->status,
        ];
    }
}
