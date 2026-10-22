<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\SmsTemplate;
use App\Models\SmsTemplateDescription;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusEnum;

class SmsTemplateRepository
{
    public function getAll(int $limit, ?string $search, ?string $sortColumn = 'template_id', ?string $sortDirection = 'desc')
    {
        $query = SmsTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'created_at', 'template_id'];
        $sortColumn = in_array($sortColumn, $allowedSorts) ? $sortColumn : 'template_id';
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortColumn, $sortDirection)->paginate($limit);
    }

    public function getAllSmsTemplates()
    {
        return SmsTemplate::with('descriptions')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    public function findByPublicId($publicId): ?SmsTemplate
    {
        $smsTemplate = SmsTemplate::findByPublicId($publicId);
        abort_if(!$smsTemplate, 404, 'Sms template not found.');
        return $smsTemplate;
    }

    public function create(array $data): SmsTemplate
    {
        return SmsTemplate::create($data);
    }

    public function createDescriptions(SmsTemplate $smsTemplate, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            SmsTemplateDescription::create([
                'template_id' => $smsTemplate->template_id,
                'language_id' => $languageId,
                'name' => $name,
                'message' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }
    public function update($publicId, array $data): ?SmsTemplate
    {
    
        $smsTemplate = $this->findByPublicId($publicId);
        $smsTemplate->update($data);
        return $smsTemplate;
    }

    public function updatePageWithDescriptions(string $publicId, array $smsTemplateData, array $descriptions): SmsTemplate {

        $smsTemplate= SmsTemplate::findByPublicId($publicId);

        $smsTemplate->update($smsTemplateData);
        
        SmsTemplateDescription::where('template_id', $smsTemplate->template_id)->delete();

        foreach ($descriptions as $description) {

            SmsTemplateDescription::create([
                'template_id' => $smsTemplate->template_id,
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'message' => $description['description']
            ]);
        }
        return $smsTemplate;
    }



    public function updateStatus(string $publicId, bool $status): bool
    {
        $smsTemplate = $this->findByPublicId($publicId);
        $smsTemplate->status = $status;
        return $smsTemplate->save();
    }

    public function delete(string $publicId): bool
    {
        $smsTemplate = $this->findByPublicId($publicId);
        return $smsTemplate->delete();
    }

}
