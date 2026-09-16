<?php

namespace Modules\Admin\Services\Logs;

use Modules\Admin\Repositories\Logs\ExceptionLogsRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



class ExceptionLogsService
{
    protected ExceptionLogsRepository $repository;

    public function __construct(ExceptionLogsRepository $repository) {
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
        $data = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($data);
    }

    private function formatResponse($data)
    {
        return [
            'public_id' => $data->public_id,
            'message'   => $data->message,
            'line'   => $data->line,
            'trace'   => $data->trace,
            'body'   => $data->body,
            'url'   => $data->url,
            'ip'   => $data->ip,
            'created_at'=> $data->created_at,   
        ];
    }
}
