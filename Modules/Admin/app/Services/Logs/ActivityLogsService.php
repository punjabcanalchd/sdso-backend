<?php

namespace Modules\Admin\Services\Logs;

use Modules\Admin\Repositories\Logs\ActivityLogsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\HasPublicId;

class ActivityLogsService
{
    use HasPublicId;
    protected ActivityLogsRepository $repository;

    public function __construct(ActivityLogsRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Get all with pagination
     */
    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $data = $this->repository->get($limit,$search,$sort_column,$sort_direction);
        $data->getCollection()->transform(function ($dataTranform) {
            return $this->formatResponse($dataTranform);
        });

        return $data;
    }


    /* ------------------------------------------------------------------
     * GET SINGLE
     * ---------------------------------------------------------------- */

    public function getById(string $publicId) {
        $id = $this->decode($publicId);
        $data = $this->repository->findByPublicId($id);
        return $this->formatResponse($data);
    }

    private function formatResponse($data)
    {
        return [
            'public_id' => $this->encodeKey($data->id),

            'log_name' => $data->log_name,

            'description' => $data->description,

            'event' => $data->event,

            'causer' => $data->causer ? [
                'public_id' => $this->encodeKey($data->causer->id),
                'name' => $data->causer->name ?? null,
            ] : null,

            'subject' => $data->subject ? [
                'public_id' => isset($data->subject->id)
                    ? $this->encodeKey($data->subject->id)
                    : null,
                'data' => $data->subject->toArray(),
            ] : [
                'public_id' => null,
                'table_name' => null,
                'data' => null,
            ],

            'table_name' => $data->subject?->getTable(),

            'attribute_changes' => $data->attribute_changes,

            'properties' => $data->properties,

            'created_at' => $data->created_at,

            'updated_at' => $data->updated_at,
        ];
    }

}
