<?php

namespace Modules\Admin\Services\MasterManagement;

use Modules\Admin\Repositories\MasterManagement\StateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


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
     * GET SINGLE USER
     * ---------------------------------------------------------------- */

    public function getState(string $publicId) {
        $state = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($state);
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
