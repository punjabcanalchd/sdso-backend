<?php

namespace Modules\Admin\Services\SDSO;

use Modules\Admin\Repositories\SDSO\DamHeadworksReadingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\DamDailyReading;
use App\Traits\HasPublicId;


class DamHeadWorksReadingService
{
    use HasPublicId;
    protected DamHeadworksReadingRepository $repository;

    public function __construct(DamHeadworksReadingRepository $repository) {
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

    /**
     * Get all without pagination
    */
    public function getAll()
    {
        $data = $this->repository->getAll();
        $data->transform(function ($dataTranform) {
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
        $readingDate = $data->readingdate
        ? Carbon::parse($data->readingdate)
        : null;
        return [
            'public_id' => $data->public_id,
            'damhwname'   => $data->damheadworkDescription?->damhwname,
            'damhwcode'   => $data->dam?->public_id,
            'inflow'   => $data->inflow,
            'outflow'   => $data->outflow,
            'waterlevel'   => $data->waterlevel,
            'readingdate' => $readingDate?->format('Y-m-d'),
            'start_time'  => $readingDate?->format('H:i'),
            'created_by'=> $data->user?->name,
            'role'=> $data->userrole?->name,    
        ];
    }

    public function create(array $data): DamDailyReading
    {
        // Repository handles database operation
        $readingDateTime = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['readingdate'] . ' ' . $data['start_time']
        )->format('Y-m-d H:i:s');

        $currentUser = auth()->user();

        $userID = $currentUser->id;
        $roleID = $currentUser->current_user_role;

        $publicDamHeadworksId = $data['damhwcode'];
        if ($publicDamHeadworksId) {
            try {
                $damhwcode = (int) $this->decode($publicDamHeadworksId);
            } catch (\Exception $e) {
                $damhwcode = 0;
            }
        }
        $data['damhwcode'] = $damhwcode;

        $data['readingdate'] = $readingDateTime;
        $data['user_id'] = $userID;
        $data['role_id'] = $roleID;
        $dam = $this->repository->create($data);
        return $dam;
    }

    public function update(array $data, string $publicId)
    {
        // Update the reading date and time if they are provided
        if (isset($data['readingdate']) && isset($data['start_time'])) {
            $readingDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $data['readingdate'] . ' ' . $data['start_time']
            )->format('Y-m-d H:i:s');

            $data['readingdate'] = $readingDateTime;
        }

        $currentUser = auth()->user();

        $userID = $currentUser->id;
        $roleID = $currentUser->current_user_role;

        $publicDamHeadworksId = $data['damhwcode'];
        if ($publicDamHeadworksId) {
            try {
                $damhwcode = (int) $this->decode($publicDamHeadworksId);
            } catch (\Exception $e) {
                $damhwcode = 0;
            }
        }
        $data['damhwcode'] = $damhwcode;
        $data['user_id'] = $userID;
        $data['role_id'] = $roleID;
        return $this->repository->update($publicId, $data);
    }
}
