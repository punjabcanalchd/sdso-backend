<?php

namespace Modules\Admin\Services\Others;

use Modules\Admin\Repositories\Others\SmsTemplateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\SmsTemplate;


class SmsTemplateService
{
    protected SmsTemplateRepository $repository;

    public function __construct(SmsTemplateRepository $repository) {
        $this->repository = $repository;
    }

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $smsTemplates = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $smsTemplates->getCollection()->transform(function ($smsTemplate) {
            return $this->formatResponse($smsTemplate);
        });

        return $smsTemplates;
    }


    /* ------------------------------------------------------------------
     * GET SINGLE Sms Template
     * ---------------------------------------------------------------- */

    public function getSmsTemplate(string $publicId) {
        $smsTemplate = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($smsTemplate);
    }

    private function formatResponse($smsTemplate)
    {
        $english = $smsTemplate->descriptions->firstWhere('language_id', 1);
        $punjabi = $smsTemplate->descriptions->firstWhere('language_id', 2);

        return [
            'public_id' => $smsTemplate->public_id,
            'name_en'   => $english?->name,
            'name_pb'   => $punjabi?->name,
            'description_en'   => $english?->message,
            'description_pb'   => $punjabi?->message,
            'templateid'=> $smsTemplate->templateid,
            'created_at'=> $smsTemplate->created_at,
            'status'    => $smsTemplate->status,
        ];
    }

    public function store(array $data): SmsTemplate
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
            $smsTemplate = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($smsTemplate, $translations);

            return $smsTemplate;
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
