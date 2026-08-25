<?php

namespace Modules\Admin\Services\MasterManagement;

use App\Models\States;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Repositories\MasterManagement\StateRepository;

class StateService
{
    protected StateRepository $repository;

    public function __construct(StateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getStates(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $states = $this->repository->getAll($limit, $search, $sort_column, $sort_direction);
        $states->getCollection()->transform(function ($state) {
            return $this->formatResponse($state);
        });

        return $states;
    }

    public function getAllStates()
    {
        $states = $this->repository->getAllStates();
        $states->transform(function ($state) {
            return $this->formatResponse($state);
        });

        return $states;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE STATE
     * ---------------------------------------------------------------- */

    public function getState(string $publicId)
    {
        $state = $this->repository->findByPublicId($publicId);

        return $this->formatResponse($state);
    }

    public function createState(array $data): States
    {
        return DB::transaction(function () use ($data) {

            $languages = $data['languages'] ?? [];

            unset($data['languages']);

            // Repository handles database operation
            $state = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($state, $languages);

            return $state;
        });
    }

    public function updateState(array $data, string $publicId)
    {
        return DB::transaction(function () use ($data, $publicId) {

            $stateData = [
                'lgdstatecode' => $data['lgdstatecode'],
                'status' => $data['status'],
            ];

            $descriptions = $data['languages'] ?? [];

            return $this->repository->update(
                $publicId,
                $stateData,
                $descriptions
            );
        });
    }

    private function formatResponse($state)
    {
        $english = $state->description->firstWhere('language_id', 1);
        $punjabi = $state->description->firstWhere('language_id', 2);

        return [
            'public_id' => $state->public_id,
            'name_en' => $english?->name,
            'name_pb' => $punjabi?->name,
            'description_en' => $english?->description,
            'description_pb' => $punjabi?->description,
            'lgdstatecode' => $state->lgdstatecode,
            'created_at' => $state->created_at,
            'status' => $state->status,
        ];
    }
}
