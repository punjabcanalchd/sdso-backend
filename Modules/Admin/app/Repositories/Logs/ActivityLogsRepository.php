<?php

namespace Modules\Admin\Repositories\Logs;

use Spatie\Activitylog\Models\Activity;

class ActivityLogsRepository
{
    /* ------------------------------------------------------------------
     * GET ALL
     * ---------------------------------------------------------------- */

    public function get(
        int $limit,
        ?string $search,
        ?string $sort_column,
        ?string $sort_direction
    ) {
        $query = Activity::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'ILIKE', "%{$search}%")
                    ->orWhere('log_name', 'ILIKE', "%{$search}%");
            });
        }

        $sort_direction = strtolower($sort_direction ?? 'desc');

        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'desc';
        }

        $allowedColumns = [
            'id',
            'log_name',
            'description',
            'event',
            'created_at',
        ];

        $sort_column = in_array($sort_column, $allowedColumns)
            ? $sort_column
            : 'id';

        $query->orderBy($sort_column, $sort_direction);

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE BY ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(int $id): Activity
    {
        return Activity::findOrFail($id);
    }
}