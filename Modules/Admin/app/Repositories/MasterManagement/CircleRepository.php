<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Circles;

class CircleRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Circles
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Circles::with(['description']);
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

            $query->leftJoin('circles_descriptions as cd', function ($join) {
                $join->on('cd.circle_id', '=', 'circles.circle_id')
                    ->where('cd.language_id', 1); // English
            });

            $query->select('circles.*')->orderBy('cd.name', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('circles_descriptions as cd', function ($join) {
                $join->on('cd.circle_id', '=', 'circles.circle_id')
                    ->where('cd.language_id', 2); // Punjabi
            });

            $query->select('circles.*')->orderBy('cd.name', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'circle_id', $sort_direction);
        }

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Circle BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Circles
    {
        $circle = Circles::findByPublicId($publicId);
        abort_if(!$circle, 404, 'Circle not found.');
        return $circle;
    }

    /* ------------------------------------------------------------------
     * CREATE Circle
     * ---------------------------------------------------------------- */

    public function create(array $data): Circles
    {
        return Circles::create($data);
    }

    /* ------------------------------------------------------------------
     * UPDATE Circle
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Circles
    {
        $circle = $this->findByPublicId($publicId);
        $circle->update($data);
        return $circle;
    }

    /* ------------------------------------------------------------------
     * DELETE Circle
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $circle = $this->findByPublicId($publicId);
        return $circle->delete();
    }
}