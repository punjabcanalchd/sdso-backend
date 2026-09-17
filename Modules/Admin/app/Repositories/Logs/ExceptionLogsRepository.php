<?php

namespace Modules\Admin\Repositories\Logs;

use App\Models\ExceptionLog;

class ExceptionLogsRepository
{
    /* ------------------------------------------------------------------
     * GET ALL
     * ---------------------------------------------------------------- */

    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = ExceptionLog::query();
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'ILIKE', "%{$search}%")
                    ->orWhere('body', 'ILIKE', "%{$search}%")
                    ->orWhere('ip', 'ILIKE', "%{$search}%")
                    ->orWhere('url', 'ILIKE', "%{$search}%");
            });
        }
        $sort_direction = strtolower($sort_direction ?? 'asc');
        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'asc';
        }
        
        $query->orderBy($sort_column ?: 'id', $sort_direction);

        return $query->paginate($limit);
    }

    /* ------------------------------------------------------------------
     * GET SINGLE BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): ExceptionLog
    {
        $data = ExceptionLog::findByPublicId($publicId);
        abort_if(!$data, 404, 'Data not found.');
        return $data;
    }
}