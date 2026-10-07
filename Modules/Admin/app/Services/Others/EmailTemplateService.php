<?php

namespace Modules\Admin\Services\Others;

use Modules\Admin\Repositories\Others\EmailTemplateRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\EmailTemplate;


class EmailTemplateService
{
    protected EmailTemplateRepository $repository;

    public function __construct(EmailTemplateRepository $repository) {
        $this->repository = $repository;
    }

    public function getAll(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $emailTemplates = $this->repository->getAll($limit,$search,$sort_column,$sort_direction);
        $emailTemplates->getCollection()->transform(function ($emailTemplate) {
            return $this->formatResponse($emailTemplate);
        });

        return $emailTemplates;
    }

    public function getAllEmailTemplates()
    {
        $emailTemplates = $this->repository->getAllEmailTemplates();
        $emailTemplates->transform(function ($emailTemplate) {
            return $this->formatResponse($emailTemplate);
        });

        return $emailTemplates;
    }

    /* ------------------------------------------------------------------
     * GET SINGLE Office Hierarchy
     * ---------------------------------------------------------------- */

    public function getEmailTemplate(string $publicId) {
        $emailTemplate = $this->repository->findByPublicId($publicId);
        return $this->formatResponse($emailTemplate);
    }

    private function formatResponse($emailTemplate)
    {
        $english = $emailTemplate->descriptions->firstWhere('language_id', 1);
        $punjabi = $emailTemplate->descriptions->firstWhere('language_id', 2);

        return [
            'public_id' => $emailTemplate->public_id,
            'name_en'   => $english?->subject,
            'name_pb'   => $punjabi?->subject,
            'description_en'   => $english?->message,
            'description_pb'   => $punjabi?->message,
            'name'=> $emailTemplate->name,
            'created_at'=> $emailTemplate->created_at,
            'status'    => $emailTemplate->status,
        ];
    }

    public function store(array $data): EmailTemplate
    {
        return DB::transaction(function () use ($data) {

            $translations = [
                'subject' => $data['subject'] ?? [],
                'description' => $data['description'] ?? [],
            ];

            unset(
                $data['subject'],
                $data['description'],
            );

            // Repository handles database operation
            $emailTemplate = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($emailTemplate, $translations);

            return $emailTemplate;
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
                'subject' => $name,
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
