<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\OfficeRepository;
use App\Models\Office;
use App\Models\OfficeHierarchiesDescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\HasPublicId;

class OfficeService
{
    use HasPublicId;
    protected OfficeRepository $repository;

    public function __construct(OfficeRepository $repository) {
        $this->repository = $repository;
    }

    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $offices = $this->repository->get($limit,$search,$sort_column,$sort_direction);
        $offices->getCollection()->transform(function ($office) {
            return $this->formatResponse($office);
        });

        return $offices;
    }

    /**
     * Get all without pagination
    */
    public function getAll()
    {
        $data = $this->repository->getAll();
        $data->transform(function ($dataTranform) {
            return $this->formatResponse($dataTranform);
        });

        return $data;
    }

    /* ------------------------------------------------------------------
     * Get Offices By District
     * ---------------------------------------------------------------- */
    public function getOffficesByDistrict(string $publicId) {
        $district_id = (int) $this->decode($publicId);
        $offices = $this->repository->getOffficesByDistrict($district_id);
        $offices->transform(function ($office) {
            return $this->formatResponse($office);
        });

        return $offices;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE USER
     * ---------------------------------------------------------------- */

    public function getByPublicId(string $publicId) {
        $office = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($office);
    }

    public function createOffice(array $data): Office
    {
        return DB::transaction(function () use ($data) {
            $translations = [
                'name'        => $data['name'] ?? [],
                'description' => $data['description'] ?? [],
            ];

            unset($data['name'], $data['description']);

            $data = $this->resolveForeignKeys($data);

            $office = $this->repository->create($data);
            $this->repository->createDescriptions($office, $translations);

            return $office;
        });
    }

    public function updateOffice(array $data, string $publicId): Office
    {
        $names = $data['name'] ?? [];
        $descriptions = $data['description'] ?? [];

        unset($data['name'], $data['description']);

        $data = $this->resolveForeignKeys($data);

        $descriptionRecords = [];
        foreach ($names as $languageId => $name) {
            $descriptionRecords[] = [
                'language_id'   => $languageId,
                'officename'    => $name,
                'officeaddress' => $descriptions[$languageId] ?? null,
            ];
        }

        return DB::transaction(function () use ($publicId, $data, $descriptionRecords) {
            return $this->repository->updateOfficeWithDescriptions($publicId, $data, $descriptionRecords);
        });
    }

    public function deleteOffice(string $publicId): bool
    {
        return DB::transaction(function () use ($publicId) {
            return $this->repository->delete($publicId);
        });
    }

    private function resolveForeignKeys(array $data): array
    {
        // 1. officelevelcode
        if (!empty($data['officelevelcode'])) {
            if (is_numeric($data['officelevelcode'])) {
                $data['officelevelcode'] = (int) $data['officelevelcode'];
            } else {
                try {
                    $data['officelevelcode'] = (int) $this->decode($data['officelevelcode']);
                } catch (\Exception $e) {
                    $level = OfficeHierarchiesDescription::where('officelevel', 'ILIKE', $data['officelevelcode'])->first();
                    $data['officelevelcode'] = $level ? $level->officelevelcode : 0;
                }
            }
        } else {
            $data['officelevelcode'] = 0;
        }

        // 2. circle_id
        if (!empty($data['circle_id'])) {
            try {
                $data['circle_id'] = (int) $this->decode($data['circle_id']);
            } catch (\Exception $e) {
                $data['circle_id'] = 0;
            }
        } else {
            $data['circle_id'] = 0;
        }

        // 3. division_id
        if (!empty($data['division_id'])) {
            try {
                $data['division_id'] = (int) $this->decode($data['division_id']);
            } catch (\Exception $e) {
                $data['division_id'] = 0;
            }
        } else {
            $data['division_id'] = 0;
        }

        // 4. subdivision_id
        if (!empty($data['subdivision_id'])) {
            try {
                $data['subdivision_id'] = (int) $this->decode($data['subdivision_id']);
            } catch (\Exception $e) {
                $data['subdivision_id'] = 0;
            }
        } else {
            $data['subdivision_id'] = 0;
        }

        // 5. lgdstatecode
        if (!empty($data['lgdstatecode'])) {
            try {
                $data['lgdstatecode'] = (int) $this->decode($data['lgdstatecode']);
            } catch (\Exception $e) {
                $data['lgdstatecode'] = 0;
            }
        } else {
            $data['lgdstatecode'] = 0;
        }

        // 6. lgddistcode
        if (!empty($data['lgddistcode'])) {
            try {
                $data['lgddistcode'] = (int) $this->decode($data['lgddistcode']);
            } catch (\Exception $e) {
                $data['lgddistcode'] = 0;
            }
        } else {
            $data['lgddistcode'] = 0;
        }

        // 7. status
        $data['status'] = !empty($data['status']) ? 1 : 0;

        return $data;
    }

    private function formatResponse($office)
    {
        $english = $office->description->firstWhere('language_id', 1);
        $punjabi = $office->description->firstWhere('language_id', 2);

        return [
            'public_id'       => $office->public_id,
            'name_en'         => $english?->officename,
            'name_pb'         => $punjabi?->officename,
            'description_en'  => $english?->officeaddress,
            'description_pb'  => $punjabi?->officeaddress,
            'state'           => $office->state?->stateDescription?->name,
            'lgdstatecode'    => $office->lgdstatecode ? $this->encodeKey($office->lgdstatecode) : null,
            'district'        => $office->district?->districtDescription?->name,
            'lgddistcode'     => $office->lgddistcode ? $this->encodeKey($office->lgddistcode) : null,
            'district_code'   => $office->lgddistcode,
            'circle'          => $office->circle?->circleDescription?->name,
            'circle_id'       => $office->circle?->public_id,
            'division'        => $office->division?->divisionDescription?->name,
            'division_id'     => $office->division?->public_id,
            'subdivision'     => $office->subdivision?->subdivisionDescription?->name,
            'subdivision_id'  => $office->subdivision?->public_id,
            'officelevel'     => $office->officeHierarchy?->officehierarchiesDescription?->officelevel,
            'officelevelcode' => $office->officeHierarchy?->public_id,
            'email'           => $office->email,
            'phonelandline'   => $office->phonelandline,
            'mobilenumber'    => $office->mobilenumber,
            'mobile'          => $office->mobilenumber,
            'pincode'         => $office->pincode,
            'created_at'      => $office->created_at,
            'status'          => $office->status,
        ];
    }

    public function getOfficesByHierarchy(string $publicId) {
        $subdivision_id = (int) $this->decode($publicId);
        $offices = $this->repository->getOfficesByHierarchy($subdivision_id);
        $offices->transform(function ($office) {
            return $this->formatResponse($office);
        });
        return $offices;
    }
}
