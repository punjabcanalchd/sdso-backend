<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Office;

class OfficeRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Offices
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
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

    /* ------------------------------------------------------------------
     * UPDATE Office
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Office
    {
        $office = $this->findByPublicId($publicId);
        $office->update($data);
        return $office;
    }

    /* ------------------------------------------------------------------
     * DELETE Office
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $office = $this->findByPublicId($publicId);
        return $office->delete();
    }
}