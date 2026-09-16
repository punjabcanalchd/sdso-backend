<?php

namespace Modules\Admin\Services\SDSO;

use Modules\Admin\Repositories\SDSO\DamHeadworksRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\DamHeadwork;
use App\Traits\HasPublicId;


class DamHeadWorksService
{
    use HasPublicId;
    protected DamHeadworksRepository $repository;

    public function __construct(DamHeadworksRepository $repository) {
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
        $english = $data->description->firstWhere('language_id', 1);
        $punjabi = $data->description->firstWhere('language_id', 2);

        return [
            'public_id' => $data->public_id,
            'name_en'   => $english?->damhwname,
            'name_pb'   => $punjabi?->damhwname,
            'description_en'   => $english?->description,
            'description_pb'   => $punjabi?->description,
            'lgdstatecode'=> $this->encodeKey($data->district?->state?->lgdstatecode),
            'district'=> $data->district?->districtsDescription?->name,
            'lgddistcode'=> $this->encodeKey($data->district?->lgddistcode),
            'office'=> $data->office?->officeDescription?->officename,
            'officecode'=> $data->office?->public_id,
            'startlat'=> $data->startlat,
            'startlong'=> $data->startlong,
            'entitycode'=> $data->entitycode == 3 ? "DAM" : "HeadWorks",
            'created_at'=> $data->created_at,
            'status'    => $data->status,
        ];
    }

    public function create(array $data): DamHeadwork
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

            $lgddistcode = $data['lgddistcode'];
            $lgdstatecode = $data['lgdstatecode'];
            $officecode = $data['officecode'];
            $entitycode = $data['entitycode'];
            try {
                $lgddistcode = (int) $this->decode($lgddistcode);
                $lgdstatecode = (int) $this->decode($lgdstatecode);
                $officecode = (int) $this->decode($officecode);
            } catch (\Exception $e) {
                $lgddistcode = 0;
                $lgdstatecode = 0;
                $officecode = 0;
            }
            $data['lgddistcode'] = $lgddistcode;
            $data['lgdstatecode'] = $lgdstatecode;
            $data['officecode'] = $officecode;
            $data['entitycode'] = $entitycode == "DAM" ? 3 : 4;

            // Repository handles database operation
            $dam = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($dam, $translations);

            return $dam;
        });
    }

    public function update(array $data, string $publicId)
    {

        $names = $data['name'] ?? [];
        $description = $data['description'] ?? [];

        unset(
            $data['name'],
            $data['description'],
        );

        $lgddistcode = $data['lgddistcode'];
        $lgdstatecode = $data['lgdstatecode'];
        $officecode = $data['officecode'];
        $entitycode = $data['entitycode'];
        try {
            $lgddistcode = (int) $this->decode($lgddistcode);
            $lgdstatecode = (int) $this->decode($lgdstatecode);
            $officecode = (int) $this->decode($officecode);
        } catch (\Exception $e) {
            $lgddistcode = 0;
            $lgdstatecode = 0;
            $officecode = 0;
        }
        $data['lgddistcode'] = $lgddistcode;
        $data['lgdstatecode'] = $lgdstatecode;
        $data['officecode'] = $officecode;
        $data['entitycode'] = $entitycode == "DAM" ? 3 : 4;

        $descriptions = [];

        foreach ($names as $languageId => $name) {

            $descriptions[] = [
                'language_id' => $languageId,
                'name' => $name,
                'description' => $description[$languageId] ?? null,
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
}
