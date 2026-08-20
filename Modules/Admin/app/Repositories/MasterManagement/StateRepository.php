<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Models\States;
use App\Models\StatesDescription;
use App\Enums\StatusEnum;

class StateRepository
{
    /* ------------------------------------------------------------------
     * GET ALL States
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = States::with('description');
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

            $query->leftJoin('states_descriptions as sd', function ($join) {
                $join->on('sd.lgdstatecode', '=', 'states.lgdstatecode')
                    ->where('sd.language_id', 1); // English
            });

            $query->select('states.*')->orderBy('sd.name', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('states_descriptions as sd', function ($join) {
                $join->on('sd.lgdstatecode', '=', 'states.lgdstatecode')
                    ->where('sd.language_id', 2); // Punjabi
            });

            $query->select('states.*')->orderBy('sd.name', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'state_id', $sort_direction);

        }

        return $query->paginate($limit);
    }

    public function getAllStates()
    {
        return States::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET SINGLE State BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): States
    {
        $state = States::findByPublicId($publicId);
        abort_if(!$state, 404, 'State not found.');
        return $state;
    }

    /* ------------------------------------------------------------------
     * CREATE State
     * ---------------------------------------------------------------- */

    public function create(array $data): States
    {
        return States::create($data);
    }

    public function createDescriptions(States $state, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            StatesDescription::create([
                'state_id' => $state->state_id,
                'lgdstatecode' => $state->lgdstatecode,
                'language_id' => $languageId,
                'name' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE State
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): States
    {
        $state = $this->findByPublicId($publicId);
        $state->update($data);
        return $state;
    }

    public function updatePageWithDescriptions(string $publicId, array $stateData, array $descriptions): States {

        $state= States::findByPublicId($publicId);

        $state->update($stateData);
        
        StatesDescription::where('lgdstatecode', $stateData['lgdstatecode'])->delete();

        foreach ($descriptions as $description) {

            StatesDescription::create([
                'lgdstatecode' => $stateData['lgdstatecode'],
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'description' => $description['description']
            ]);
        }
        return $state;
    }

    /* ------------------------------------------------------------------
     * DELETE State
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $state = $this->findByPublicId($publicId);
        return $state->delete();
    }
}