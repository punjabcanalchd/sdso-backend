<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\OfficeHierarchies;

class OfficeHierarchyRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Office Hierarchies
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = OfficeHierarchies::with(['description']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('description', function ($q) use ($search) {
                $q->where('officelevel', 'ILIKE', "%{$search}%");
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

            $query->leftJoin('office_hierarchies_descriptions as sd', function ($join) {
                $join->on('sd.officelevelcode', '=', 'office_hierarchies.officelevelcode')
                    ->where('sd.language_id', 1); // English
            });

            $query->select('office_hierarchies.*')->orderBy('sd.officelevel', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('office_hierarchies_descriptions as sd', function ($join) {
                $join->on('sd.officelevelcode', '=', 'office_hierarchies.officelevelcode')
                    ->where('sd.language_id', 2); // Punjabi
            });

            $query->select('office_hierarchies.*')->orderBy('sd.officelevel', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'officelevelcode', $sort_direction);

        }

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Office Hierarchy BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): OfficeHierarchies
    {
        $officeHierarchy = OfficeHierarchies::findByPublicId($publicId);
        abort_if(!$officeHierarchy, 404, 'State not found.');
        return $officeHierarchy;
    }

    /* ------------------------------------------------------------------
     * CREATE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function create(array $data): OfficeHierarchies
    {
        return OfficeHierarchies::create($data);
    }

    /* ------------------------------------------------------------------
     * UPDATE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): OfficeHierarchies
    {
        $officeHierarchy = $this->findByPublicId($publicId);
        $officeHierarchy->update($data);
        return $officeHierarchy;
    }

    /* ------------------------------------------------------------------
     * DELETE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $officeHierarchy = $this->findByPublicId($publicId);
        return $officeHierarchy->delete();
    }
}