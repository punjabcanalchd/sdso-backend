<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Designation;
use App\Models\DesignationDescription;

class DesignationRepository
{
    /* ------------------------------------------------------------------
     * GET ALL Designations
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = Designation::with(['description']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('designation', function ($q) use ($search) {
                $q->where('designation', 'ILIKE', "%{$search}%");
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

            $query->leftJoin('designations_descriptions as sd', function ($join) {
                $join->on('sd.desigcode', '=', 'designations.desigcode')
                    ->where('sd.language_id', 1); // English
            });

            $query->select('designations.*')->orderBy('sd.officelevel', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('designations_descriptions as sd', function ($join) {
                $join->on('sd.desigcode', '=', 'designations.desigcode')
                    ->where('sd.language_id', 2); // Punjabi
            });

            $query->select('designations.*')->orderBy('sd.officelevel', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'desigcode', $sort_direction);

        }

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Designation BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): Designation
    {
        $designation = Designation::findByPublicId($publicId);
        abort_if(!$designation, 404, 'Designation not found.');
        return $designation;
    }

    /* ------------------------------------------------------------------
     * CREATE Designation
     * ---------------------------------------------------------------- */

    public function create(array $data): Designation
    {
        return Designation::create($data);
    }

     public function createDescriptions(Designation $designations, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            DesignationDescription::create([
                'desigcode' => $designations->desigcode,
                'language_id' => $languageId,
                'designation' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE Designation
     * ---------------------------------------------------------------- */

    public function updatePageWithDescriptions(string $publicId, array $designationData, array $descriptions): Designation {

        $designation= Designation::findByPublicId($publicId);

        $designation->update($designationData);
        
        DesignationDescription::where('desigcode', $designation->desigcode)->delete();

        foreach ($descriptions as $description) {

            DesignationDescription::create([
                'desigcode' => $designation->desigcode,
                'language_id' => $description['language_id'],
                'designation' => $description['name'],
                'description' => $description['description']
            ]);
        }
        return $designation;
    }

    /* ------------------------------------------------------------------
     * DELETE Designation
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $designation = $this->findByPublicId($publicId);
        return $designation->delete();
    }
}