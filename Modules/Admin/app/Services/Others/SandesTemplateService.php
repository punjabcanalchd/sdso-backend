<?php

namespace Modules\Admin\Services\Others;

use Modules\Admin\Repositories\Others\SandesTemplateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\SandesTemplate;


class SandesTemplateService
{
    protected SandesTemplateRepository $repository;

    public function __construct(SandesTemplateRepository $repository) {
        $this->repository = $repository;
    }

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $sandesTemplates = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $sandesTemplates->getCollection()->transform(function ($sandesTemplate) {
            return $this->formatResponse($sandesTemplate);
        });

        return $sandesTemplates;
    }


    /* ------------------------------------------------------------------
     * GET SINGLE Sandes Template
     * ---------------------------------------------------------------- */

    public function getSandesTemplate(string $publicId) {
        $sandesTemplate = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($sandesTemplate);
    }

    private function formatResponse($sandesTemplate)
    {
        $english = $sandesTemplate->descriptions->firstWhere('language_id', 1);
        $punjabi = $sandesTemplate->descriptions->firstWhere('language_id', 2);

        return [
            'public_id' => $sandesTemplate->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->message,
            'description_pb'   => $punjabi?->message,
            'created_at'=> $sandesTemplate->created_at,
            'status'    => $sandesTemplate->status,
        ];
    }

    public function store(array $data): SandesTemplate
    {
        return DB::transaction(function () use ($data) {

            $translations = [
                'name' => $data['subject'] ?? [],
                'description' => $data['description'] ?? [],
            ];

            unset(
                $data['subject'],
                $data['description'],
            );

            // Repository handles database operation
            $sandesTemplate = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($sandesTemplate, $translations);

            return $sandesTemplate;
        });
    }

    public function update(array $data, string $publicId)
    {

        $names = $data['subject'] ?? [];
        $description = $data['description'] ?? [];

        unset(
            $data['subject'],
            $data['description'],
        );

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

    public function updateStatus(string $publicId, bool $status)
    {
        return $this->repository->updateStatus($publicId, $status);
    }

    public function delete(string $publicId)
    {
        return $this->repository->delete($publicId);
    }
}
