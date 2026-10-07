<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateDescription;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusEnum;

class EmailTemplateRepository
{
    public function getAll(int $limit, ?string $search, ?string $sortColumn = 'template_id', ?string $sortDirection = 'desc')
    {
        $query = EmailTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'display_on_home_page', 'created_at', 'template_id'];
        $sortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'template_id';
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit);
    }

    public function getAllEmailTemplates()
    {
        return EmailTemplate::with('descriptions')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    public function findByPublicId($publicId): ?EmailTemplate
    {
        $emailTemplate = EmailTemplate::findByPublicId($publicId);
        abort_if(!$emailTemplate, 404, 'Email template not found.');
        return $emailTemplate;
    }

    public function create(array $data): EmailTemplate
    {
        return EmailTemplate::create($data);
    }

    public function createDescriptions(EmailTemplate $emailTemplate, array $translations): void {
        foreach ($translations['subject'] as $languageId => $subject) {
            EmailTemplateDescription::create([
                'template_id' => $emailTemplate->template_id,
                'language_id' => $languageId,
                'subject' => $subject,
                'message' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }
    public function update($publicId, array $data): ?EmailTemplate
    {
        $emailTemplate = $this->findByPublicId($publicId);
        $emailTemplate->update($data);
        return $emailTemplate;
    }

    public function updatePageWithDescriptions(string $publicId, array $emailTemplateData, array $descriptions): EmailTemplate {

        $emailTemplate= EmailTemplate::findByPublicId($publicId);

        $emailTemplate->update($emailTemplateData);
        
        EmailTemplateDescription::where('template_id', $emailTemplate->template_id)->delete();

        foreach ($descriptions as $description) {

            EmailTemplateDescription::create([
                'template_id' => $emailTemplate->template_id,
                'language_id' => $description['language_id'],
                'subject' => $description['subject'],
                'message' => $description['description']
            ]);
        }
        return $emailTemplate;
    }



    public function updateStatus(string $publicId, bool $status): bool
    {
        $circle = $this->findByPublicId($publicId);
        $circle->status = $status;
        return $circle->save();
    }

    public function delete(string $publicId): bool
    {
        $emailTemplate = $this->findByPublicId($publicId);
        return $emailTemplate->delete();
    }

}