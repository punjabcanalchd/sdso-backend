<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\DistrictRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DistrictService
{
    protected DistrictRepository $repository;

    public function __construct(DistrictRepository $repository) {
        $this->repository = $repository;
    }

    public function getDistricts(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $districts = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $districts->getCollection()->transform(function ($district) {
            return $this->formatState($district);
        });

        return $districts;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE District
     * ---------------------------------------------------------------- */

    public function getDistrict(string $publicId) {
        $district = $this->repository->findByPublicId($publicId);
        return $this->formatState($district);
    }

    private function formatState($district)
    {
        $english = $district->description->firstWhere('language_id', 1);
        $punjabi = $district->description->firstWhere('language_id', 2);

        return [
            'public_id' => $district->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'state'=> $district->state?->stateDescription?->name,
            'created_at'=> $district->created_at,
            'status'    => $district->status,
        ];
    }
}
