<?php

namespace Modules\Admin\Repositories\MasterManagement;

use App\Enums\StatusEnum;
use App\Models\States;
use App\Models\StatesDescription;

class StateRepository
{
    /* ------------------------------------------------------------------
     * GET ALL States
     * ---------------------------------------------------------------- */

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = States::with('description');

        // Search in both English & Punjabi

        if (! empty($search)) {
            $query->whereHas('description', function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%");
            });
        }
        $sort_direction = strtolower($sort_direction ?? 'asc');
        if (! in_array($sort_direction, ['asc', 'desc'])) {
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

        } elseif ($sort_column === 'name_pb') {

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
        abort_if(! $state, 404, 'State not found.');

        return $state;
    }

    /* ------------------------------------------------------------------
     * CREATE State
     * ---------------------------------------------------------------- */

    public function create(array $data): States
    {
        return States::create($data);
    }

    public function createDescriptions(
        States $state,
        array $languages
    ): void {

        foreach ($languages as $language) {

            StatesDescription::create([
                'state_id' => $state->state_id,
                'lgdstatecode' => $state->lgdstatecode,
                'language_id' => $language['language_id'],
                'name' => $language['name'],
                'description' => $language['description'] ?? null,
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

    public function updatePageWithDescriptions(
        string $publicId,
        array $stateData,
        array $descriptions
    ): States {

        $state = $this->findByPublicId($publicId);

        $oldLgdStateCode = $state->lgdstatecode;
        $newLgdStateCode = $stateData['lgdstatecode'];

        // Update main state
        $state->update([
            'lgdstatecode' => $newLgdStateCode,
            'status' => $stateData['status'],
        ]);

        // Update descriptions
        foreach ($descriptions as $description) {

            StatesDescription::updateOrCreate(
                [
                    'lgdstatecode' => $oldLgdStateCode,
                    'language_id' => $description['language_id'],
                ],
                [
                    'lgdstatecode' => $newLgdStateCode,
                    'name' => $description['name'],
                    'description' => $description['description'],
                ]
            );
        }

        return $state->fresh();
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
