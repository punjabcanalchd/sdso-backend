<?php

namespace Modules\Admin\Repositories\SDSO;

use App\Models\DamHeadwork;
use App\Models\DamHeadworkDescription;
use App\Enums\StatusEnum;

class DamHeadworksRepository
{
    /* ------------------------------------------------------------------
     * GET ALL
     * ---------------------------------------------------------------- */

    public function get(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $query = DamHeadwork::with(['description','district','office']);
        // Search in both English & Punjabi
        if (!empty($search)) {
            $query->whereHas('description', function ($q) use ($search) {
                $q->where('damhwname', 'ILIKE', "%{$search}%");
            });
        }
        $sort_direction = strtolower($sort_direction ?? 'asc');
        if (!in_array($sort_direction, ['asc', 'desc'])) {
            $sort_direction = 'asc';
        }
        /*
        |-------------------------------------------------------------
        | Sort by English/Punjabi name
        |-------------------------------------------------------------
        */
        if ($sort_column === 'name_en') {

            $query->leftJoin('damheadwork_descriptions as dd', function ($join) {
                $join->on('dd.damhwcode', '=', 'damheadworks.damhwcode')
                    ->where('dd.language_id', 1); // English
            });

            $query->select('damheadworks.*')->orderBy('dd.damhwname', $sort_direction);

        } else if($sort_column === 'name_pb') {

            $query->leftJoin('damheadwork_descriptions as dd', function ($join) {
                $join->on('dd.damhwcode', '=', 'damheadworks.damhwcode')
                    ->where('dd.language_id', 2); // Punjabi
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
        return DamHeadwork::with('description')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    /* ------------------------------------------------------------------
     * GET SINGLE BY PUBLIC ID
     * ---------------------------------------------------------------- */

    public function findByPublicId(string $publicId): DamHeadwork
    {
        $data = DamHeadwork::findByPublicId($publicId);
        abort_if(!$data, 404, 'Data not found.');
        return $data;
    }

    /* ------------------------------------------------------------------
     * CREATE
     * ---------------------------------------------------------------- */

    public function create(array $data): DamHeadwork
    {
        return DamHeadwork::create($data);
    }

    public function createDescriptions(DamHeadwork $dams, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            DamHeadworkDescription::create([
                'damhwcode' => $dams->damhwcode,
                'language_id' => $languageId,
                'damhwname' => $name,
                'description' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }

    /* ------------------------------------------------------------------
     * UPDATE
     * ---------------------------------------------------------------- */

    public function update(string $publicId, array $data): DamHeadwork
    {
        $dam = $this->findByPublicId($publicId);
        $dam->update($data);
        return $dam;
    }

    public function updatePageWithDescriptions(string $publicId, array $damsData, array $descriptions): DamHeadwork {

        $dam= DamHeadwork::findByPublicId($publicId);

        $dam->update($damsData);
        
        DamHeadworkDescription::where('damhwcode', $dam->damhwcode)->delete();

        foreach ($descriptions as $description) {

            DamHeadworkDescription::create([
                'damhwcode' => $dam->damhwcode,
                'language_id' => $description['language_id'],
                'damhwname' => $description['name'],
                'description' => $description['description']
            ]);
        }
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