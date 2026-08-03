<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\OfficeHierarchyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


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
            'officesenioritylevel'=> $officeHierarchy->officesenioritylevel,
            'created_at'=> $officeHierarchy->created_at,
            'status'    => $officeHierarchy->status,
        ];
    }
}
