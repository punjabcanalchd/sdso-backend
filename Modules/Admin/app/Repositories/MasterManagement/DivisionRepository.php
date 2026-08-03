<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Divisions;

class DivisionRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Divisions
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Divisions::with(['description']);
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

            $query->leftJoin('divisions_descriptions as dd', function ($join) {
                $join->on('dd.division_id', '=', 'divisions.division_id')
                    ->where('dd.language_id', 1); // English
            });

            $query->select('divisions.*')->orderBy('dd.name', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('divisions_descriptions as dd', function ($join) {
                $join->on('dd.division_id', '=', 'divisions.division_id')
                    ->where('dd.language_id', 2); // Punjabi
            });

            $query->select('divisions.*')->orderBy('dd.name', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'division_id', $sort_direction);
        }

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Division BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Divisions
    {
        $division = Divisions::findByPublicId($publicId);
        abort_if(!$division, 404, 'Division not found.');
        return $division;
    }

    /* ------------------------------------------------------------------
     * CREATE Division
     * ---------------------------------------------------------------- */

    public function create(array $data): Divisions
    {
        return Divisions::create($data);
    }

    /* ------------------------------------------------------------------
     * UPDATE Division
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Divisions
    {
        $division = $this->findByPublicId($publicId);
        $division->update($data);
        return $division;
    }

    /* ------------------------------------------------------------------
     * DELETE Division
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $division = $this->findByPublicId($publicId);
        return $division->delete();
    }
}