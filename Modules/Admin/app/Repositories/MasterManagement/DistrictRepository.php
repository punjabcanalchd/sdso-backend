<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Districts;
use App\Models\DistrictsDescription;

use App\Enums\StatusEnum;

class DistrictRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Districts
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Districts::with(['districtsDescription','state.stateDescription']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('description', function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%");
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

            $query->leftJoin('districts_descriptions as sd', function ($join) {
                $join->on('sd.lgddistcode', '=', 'districts.lgddistcode')
                    ->where('sd.language_id', 1); // English
            });

            $query->select('districts.*')->orderBy('sd.name', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('districts_descriptions as sd', function ($join) {
                $join->on('sd.lgddistcode', '=', 'districts.lgddistcode')
                    ->where('sd.language_id', 2); // Punjabi
            });

            $query->select('districts.*')->orderBy('sd.name', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'district_id', $sort_direction);

        }

        return $query->paginate($limit);
    }

    public function getAllDistricts()
    {
        return Districts::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET SINGLE District BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Districts
    {
        $district = Districts::findByPublicId($publicId);
        abort_if(!$district, 404, 'State not found.');
        return $district;
    }

    /* ------------------------------------------------------------------
     * CREATE State
     * ---------------------------------------------------------------- */

    public function create(array $data): Districts
    {
        return Districts::create($data);
    }

    public function createDescriptions(Districts $districts, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            DistrictsDescription::create([
                'state_id' => $districts->state_id,
                'lgddistcode' => $districts->lgddistcode,
                'language_id' => $languageId,
                'name' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE State
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Districts
    {
        $district = $this->findByPublicId($publicId);
        $district->update($data);
        return $district;
    }

    public function updatePageWithDescriptions(string $publicId, array $districtData, array $descriptions): Districts {

        $district= Districts::findByPublicId($publicId);

        $district->update($districtData);
        
        DistrictsDescription::where('lgddistcode', $districtData['lgddistcode'])->delete();

        foreach ($descriptions as $description) {

            DistrictsDescription::create([
                'lgddistcode' => $districtData['lgddistcode'],
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'description' => $description['description']
            ]);
        }
        return $district;
    }

    /* ------------------------------------------------------------------
     * DELETE State
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $district = $this->findByPublicId($publicId);
        return $district->delete();
    }
}