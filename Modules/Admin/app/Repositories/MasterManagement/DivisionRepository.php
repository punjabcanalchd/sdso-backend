<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\Divisions;
use App\Models\DivisionsDescription;
use App\Enums\StatusEnum;

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

    public function getAllDivisions()
    {
        return Divisions::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
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
     * GET Divisions BY Circle ID
     * ---------------------------------------------------------------- */

    public function getDivisionsByCircle(int $publicId)
    {

        $divisions = Divisions::where('circle_id', $publicId)->get();
        return $divisions;
    }

    /* ------------------------------------------------------------------
     * CREATE Division
     * ---------------------------------------------------------------- */

    public function create(array $data): Divisions
    {
        return Divisions::create($data);
    }

    public function createDescriptions(Divisions $division, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            DivisionsDescription::create([
                'division_id' => $division->division_id,
                'language_id' => $languageId,
                'name' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE Circle
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): Divisions
    {
        $division = $this->findByPublicId($publicId);
        $division->update($data);
        return $division;
    }

    public function updatePageWithDescriptions(string $publicId, array $divisionData, array $descriptions): Divisions {

        $division= Divisions::findByPublicId($publicId);


        $division->update($divisionData);
        
        DivisionsDescription::where('division_id', $division->division_id)->delete();

        foreach ($descriptions as $description) {

            DivisionsDescription::create([
                'division_id' => $division->division_id,
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'description' => $description['description']
            ]);
        }
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