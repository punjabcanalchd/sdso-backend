<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\OfficeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class OfficeService
{
    protected OfficeRepository $repository;

    public function __construct(OfficeRepository $repository) {
        $this->repository = $repository;
    }

    public function getOffices(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $offices = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $offices->getCollection()->transform(function ($office) {
            return $this->formatResponse($office);
        });

        return $offices;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE USER
     * ---------------------------------------------------------------- */

    public function getOffice(string $publicId) {
        $office = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($office);
    }

    private function formatResponse($office)
    {
        $english = $office->description->firstWhere('language_id', 1);
        $punjabi = $office->description->firstWhere('language_id', 2);

        return [
            'public_id' => $office->public_id,
            'name_en'   => $english?->officename,
            'name_pb'   => $punjabi?->officename,
            'state' => $office->state?->stateDescription?->name,
            'district' => $office->district?->districtDescription?->name,
            'circle' => $office->circle?->circleDescription?->name,
            'division' => $office->division?->divisionDescription?->name,
            'subdivision' => $office->subdivision?->subdivisionDescription?->name,
            'officelevel' => $office->officeHierarchy?->officehierarchiesDescription?->officelevel,
            'email' => $office->email,
            'mobilenumber' => $office->mobilenumber,
            'created_at' => $office->created_at,
            'status'    => $office->status,
        ];
    }
}
