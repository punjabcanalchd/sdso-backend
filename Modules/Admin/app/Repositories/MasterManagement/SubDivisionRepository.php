<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\SubDivisions;
use App\Models\SubDivisionsDescription;
use App\Enums\StatusEnum;


class SubDivisionRepository
{
    /* ------------------------------------------------------------------
     * GET ALL SubDivisions
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = SubDivisions::with(['description']);
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

            $query->leftJoin('subdivisions_descriptions as sd', function ($join) {
                $join->on('sd.subdivision_id', '=', 'subdivisions.subdivision_id')
                    ->where('sd.language_id', 1); // English
            });

            $query->select('subdivisions.*')->orderBy('sd.name', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('subdivisions_descriptions as sd', function ($join) {
                $join->on('sd.subdivision_id', '=', 'subdivisions.subdivision_id')
                    ->where('sd.language_id', 2); // Punjabi
            });

            $query->select('subdivisions.*')->orderBy('sd.name', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'subdivision_id', $sort_direction);
        }

        return $query->paginate($limit);
    }

    public function getAllSubDivisions()
    {
        return SubDivisions::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET SINGLE SubDivision BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): SubDivisions
    {
        $subdivision = SubDivisions::findByPublicId($publicId);
        abort_if(!$subdivision, 404, 'SubDivision not found.');
        return $subdivision;
    }

    /* ------------------------------------------------------------------
     * GET SubDivision BY Division ID
     * ---------------------------------------------------------------- */

    public function getSubdivisionsByDivision(int $publicId)
    {

        $subdivisions = SubDivisions::where('division_id', $publicId)->get();
        return $subdivisions;
    }

    /* ------------------------------------------------------------------
     * CREATE SubDivision
     * ---------------------------------------------------------------- */

    public function create(array $data): SubDivisions
    {
        return SubDivisions::create($data);
    }

    public function createDescriptions(SubDivisions $subdivision, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            SubDivisionsDescription::create([
                'subdivision_id' => $subdivision->subdivision_id,
                'language_id' => $languageId,
                'name' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE SubDivision
     * ---------------------------------------------------------------- */

    public function updatePageWithDescriptions(string $publicId, array $subdivisionData, array $descriptions): SubDivisions {

        $subdivision= SubDivisions::findByPublicId($publicId);

        $subdivision->update($subdivisionData);
        
        SubDivisionsDescription::where('subdivision_id', $subdivision->subdivision_id)->delete();

        foreach ($descriptions as $description) {

            SubDivisionsDescription::create([
                'subdivision_id' => $subdivision->subdivision_id,
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'description' => $description['description']
            ]);
        }
        return $subdivision;
    }

    /* ------------------------------------------------------------------
     * DELETE SubDivision
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $subdivision = $this->findByPublicId($publicId);
        return $subdivision->delete();
    }
}