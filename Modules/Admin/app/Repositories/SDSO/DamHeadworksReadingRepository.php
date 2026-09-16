<?php

namespace Modules\Admin\Repositories\SDSO;

use App\Models\DamDailyReading;
use App\Enums\StatusEnum;
use Illuminate\Support\Facades\Auth;

class DamHeadworksReadingRepository
{
    /* ------------------------------------------------------------------
     * GET ALL
     * ---------------------------------------------------------------- */

    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = DamDailyReading::with(['damheadworkDescription']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('damheadworkDescription', function ($q) use ($search) {
                $q->where('damhwname', 'ILIKE', "%{$search}%");
            });
        }
        $sort_direction = strtolower($sort_direction ?? 'asc');
        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'desc';
        }
        /*
        |-------------------------------------------------------------
        | Sort by English/Punjabi name
        |-------------------------------------------------------------
        */
        if ($sort_column === 'damhwname') {

            $query->leftJoin('damheadwork_descriptions as dd', function ($join) {
                $join->on('dd.damhwcode', '=', 'damheadworks.damhwcode')
                    ->where('dd.language_id', 1); // English
            });

            $query->select('damheadworks.*')->orderBy('dd.damhwname', $sort_direction);

        } else {
            $query->orderBy($sort_column ?: 'damhwcode', $sort_direction);
        }

        return $query->paginate($limit);
    }

    /**
     * Get all without pagination
    */

    public function getAll()
    {
        return DamDailyReading::with('damheadworkDescription')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET SINGLE BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): DamDailyReading
    {
        $data = DamDailyReading::findByPublicId($publicId);
        abort_if(!$data, 404, 'Data not found.');
        return $data;
    }

    /* ------------------------------------------------------------------
     * CREATE
     * ---------------------------------------------------------------- */

    public function create(array $data): DamDailyReading
    {
        $current_user_role = Auth::user()->current_user_role;
        $user_id = Auth::user()->id;
        $data['user_id'] = $user_id;
        $data['role_id'] = $current_user_role;
        $data['edited'] = "N";
        $data['devicetype'] = "W";
        return DamDailyReading::create($data);
    }

    /* ------------------------------------------------------------------
     * UPDATE
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): DamDailyReading
    {
        $dam = $this->findByPublicId($publicId);
        $current_user_role = Auth::user()->current_user_role;
        $user_id = Auth::user()->id;
        $data['user_id'] = $user_id;
        $data['role_id'] = $current_user_role;
        $data['edited'] = "Y";
        $data['devicetype'] = "W";
        $dam->update($data);
               
        return $dam;
    }

    /* ------------------------------------------------------------------
     * DELETE
     * ---------------------------------------------------------------- */

    public function delete(string $publicId): bool
    {
        $data = $this->findByPublicId($publicId);
        return $data->delete();
    }
}