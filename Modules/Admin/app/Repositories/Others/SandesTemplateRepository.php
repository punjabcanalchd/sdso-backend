<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\SandesTemplate;
use App\Models\SandesTemplateDescription;
use Illuminate\Support\Facades\DB;
use App\Enums\StatusEnum;

class SandesTemplateRepository
{
    public function getAll(int $limit, ?string $search, ?string $sortColumn = 'template_id', ?string $sortDirection = 'desc')
    {
        $query = SandesTemplate::with('descriptions');

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

    public function getAllSandesTemplates()
    {
        return SandesTemplate::with('descriptions')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    public function findByPublicId($publicId): ?SandesTemplate
    {
        $sandesTemplate = SandesTemplate::findByPublicId($publicId);
        abort_if(!$sandesTemplate, 404, 'Sandes template not found.');
        return $sandesTemplate;
    }

    public function create(array $data): SandesTemplate
    {
        return SandesTemplate::create($data);
    }

    public function createDescriptions(SandesTemplate $sandesTemplate, array $translations): void {
        foreach ($translations['name'] as $languageId => $name) {
            SandesTemplateDescription::create([
                'template_id' => $sandesTemplate->template_id,
                'language_id' => $languageId,
                'name' => $name,
                'message' => $translations['description'][$languageId] ?? null,
            ]);
        }
    }
    public function update($publicId, array $data): ?SandesTemplate
    {
        $sandesTemplate = $this->findByPublicId($publicId);
        $sandesTemplate->update($data);
        return $sandesTemplate;
    }

    public function updatePageWithDescriptions(string $publicId, array $sandesTemplateData, array $descriptions): SandesTemplate {

        $sandesTemplate= SandesTemplate::findByPublicId($publicId);

        $sandesTemplate->update($sandesTemplateData);
        
        SandesTemplateDescription::where('template_id', $sandesTemplate->template_id)->delete();

        foreach ($descriptions as $description) {

            SandesTemplateDescription::create([
                'template_id' => $sandesTemplate->template_id,
                'language_id' => $description['language_id'],
                'name' => $description['name'],
                'message' => $description['description']
            ]);
        }
        return $sandesTemplate;
    }



    public function updateStatus(string $publicId, bool $status): bool
    {
        $sandesTemplate = $this->findByPublicId($publicId);
        $sandesTemplate->status = $status;
        return $sandesTemplate->save();
    }

    public function delete(string $publicId): bool
    {
        $sandesTemplate = $this->findByPublicId($publicId);
        return $sandesTemplate->delete();
    }

}