<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Districts;

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

    /* ------------------------------------------------------------------
     * UPDATE State
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Districts
    {
        $district = $this->findByPublicId($publicId);
        $district->update($data);
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