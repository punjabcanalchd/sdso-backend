<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Office;
use App\Models\OfficeDescription;
use App\Enums\StatusEnum;

class OfficeRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Offices
     * ---------------------------------------------------------------- */

    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Office::with(['description']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('description', function ($q) use ($search) {
                $q->where('officename', 'ILIKE', "%{$search}%");
            });
        }
        $sort_direction = strtolower($sort_direction ?? 'asc');
        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'asc';
        }
        /*
        |-------------------------------------------------------------
        | Sort by English/Punjabi name
        |-------------------------------------------------------------
        */
        if ($sort_column === 'name_en') {

            $query->leftJoin('office_descriptions as dd', function ($join) {
                $join->on('dd.officecode', '=', 'offices.officecode')
                    ->where('dd.language_id', 1); // English
            });

            $query->select('offices.*')->orderBy('dd.officename', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('office_descriptions as dd', function ($join) {
                $join->on('dd.officecode', '=', 'offices.officecode')
                    ->where('dd.language_id', 2); // Punjabi
            });

            $query->select('offices.*')->orderBy('dd.officename', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'officecode', $sort_direction);
        }

        return $query->paginate($limit);
    }

    /**
     * Get all without pagination
    */

    public function getAll()
    {
        return Office::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET Offices By District ID
     * ---------------------------------------------------------------- */

    public function getOffficesByDistrict(int $publicId)
    {
        $offices = Office::where('lgddistcode', $publicId)->get();
        return $offices;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Office BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Office
    {
        $office = Office::findByPublicId($publicId);
        abort_if(!$office, 404, 'Office not found.');
        return $office;
    }

    /* ------------------------------------------------------------------
     * CREATE Office
     * ---------------------------------------------------------------- */

    public function create(array $data): Office
    {
        return Office::create($data);
    }

    public function createDescriptions(Office $office, array $translations): void
    {
        foreach ($translations['name'] as $languageId => $name) {
            OfficeDescription::create([
                'officecode'    => $office->officecode,
                'language_id'   => $languageId,
                'officename'    => $name,
                'officeaddress' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE Office
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Office
    {
        $office = $this->findByPublicId($publicId);
        $office->update($data);
        return $office;
    }

    public function updateOfficeWithDescriptions(string $publicId, array $officeData, array $descriptions): Office
    {
        $office = $this->findByPublicId($publicId);
        $office->update($officeData);

        OfficeDescription::where('officecode', $office->officecode)->delete();

        foreach ($descriptions as $desc) {
            OfficeDescription::create([
                'officecode'    => $office->officecode,
                'language_id'   => $desc['language_id'],
                'officename'    => $desc['officename'],
                'officeaddress' => $desc['officeaddress'] ?? null,
            ]);
        }

        return $office;
    }

    /* ------------------------------------------------------------------
     * DELETE Office
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $office = $this->findByPublicId($publicId);
        $office->status = 0;
        $office->save();
        $office->description()->delete();
        return (bool) $office->delete();
    }

    /* ------------------------------------------------------------------
     * GET OFFICES BY SUBDIVISION HIERARCHY
     * ---------------------------------------------------------------- */
    public function getOfficesByHierarchy(int $subdivision_id)
    {
        return Office::with(['description'])
                     ->where('subdivision_id', $subdivision_id)
                     ->get();
    }
}