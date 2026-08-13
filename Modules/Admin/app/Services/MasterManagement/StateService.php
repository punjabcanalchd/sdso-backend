<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\StateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\States;


class StateService
{
    protected StateRepository $repository;

    public function __construct(StateRepository $repository) {
        $this->repository = $repository;
    }

    public function getStates(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $states = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $states->getCollection()->transform(function ($state) {
            return $this->formatResponse($state);
        });

        return $states;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE STATE
     * ---------------------------------------------------------------- */

    public function getState(string $publicId) {
        $state = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($state);
    }

    public function createState(array $data): States
    {
        return DB::transaction(function () use ($data) {

            $translations = [
                'name' => $data['name'] ?? [],
                'description' => $data['description'] ?? [],
            ];

            unset(
                $data['name'],
                $data['description'],
            );

            // Repository handles database operation
            $state = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($state, $translations);

            return $state;
        });
    }

    public function updateState(array $data, string $publicId)
    {

        $names = $data['name'] ?? [];
        $descriptions = $data['description'] ?? [];

    
        unset(
            $data['name'],
            $data['description'],
        );

        $descriptions = [];

        foreach ($names as $languageId => $name) {

            $descriptions[] = [
                'language_id' => $languageId,
                'name' => $name,
                'description' => $descriptions[$languageId] ?? null,
            ];
        }
        DB::transaction(function () use ($publicId, $data, $descriptions) {

            return $this->repository->updatePageWithDescriptions(
                $publicId,
                $data,
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
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'lgdstatecode'=> $state->lgdstatecode,
            'created_at'=> $state->created_at,
            'status'    => $state->status,
        ];
    }
}
