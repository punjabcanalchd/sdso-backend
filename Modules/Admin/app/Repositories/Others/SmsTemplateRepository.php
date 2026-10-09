<?php

namespace Modules\Admin\Repositories\Others;

use App\Models\SmsTemplate;
use App\Models\SmsTemplateDescription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SmsTemplateRepository
{
    protected SmsTemplate $model;

    public function __construct(SmsTemplate $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated SMS templates with search and sorting.
     */
    public function getAll(
        int $limit = 25,
        ?string $search = null,
        string $sortColumn = 'template_id',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        $query = $this->model->with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('templateid', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'created_at', 'template_id', 'templateid'];
        $sort = in_array($sortColumn, $allowedSorts, true) ? $sortColumn : 'template_id';
        $direction = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sort, $direction)->paginate($limit);
    }

    /**
     * Find template by public_id or template_id.
     */
    public function findById(int|string $id): ?SmsTemplate
    {
        if (is_numeric($id)) {
            return SmsTemplate::with('descriptions')->find((int) $id);
        }

        $template = SmsTemplate::findByPublicId((string) $id);
        if ($template) {
            $template->load('descriptions');
        }

        return $template;
    }

    /**
     * Create a new SMS template.
     */
    public function create(array $data): SmsTemplate
    {
        return $this->model->create([
            'name'       => trim($data['name']),
            'templateid' => trim($data['templateid']),
            'status'     => (bool) ($data['status'] ?? true),
        ]);
    }

    /**
     * Update an SMS template.
     */
    public function update(SmsTemplate $template, array $data): SmsTemplate
    {
        if (array_key_exists('name', $data) && $data['name'] !== null) {
            $template->name = trim($data['name']);
        }

        if (array_key_exists('templateid', $data) && $data['templateid'] !== null) {
            $template->templateid = trim($data['templateid']);
        }

        if (array_key_exists('status', $data) && $data['status'] !== null) {
            $template->status = (bool) $data['status'];
        }

        $template->save();

        return $template;
    }

    /**
     * Update or create a description for a specific language.
     */
    public function updateOrCreateDescription(int $templateId, int $languageId, string $message): SmsTemplateDescription
    {
        return SmsTemplateDescription::updateOrCreate(
            ['template_id' => $templateId, 'language_id' => $languageId],
            ['message' => $message]
        );
    }

    /**
     * Update status of SMS template.
     */
    public function updateStatus(SmsTemplate $template, bool $status): bool
    {
        $template->status = $status;
        return $template->save();
    }
}
