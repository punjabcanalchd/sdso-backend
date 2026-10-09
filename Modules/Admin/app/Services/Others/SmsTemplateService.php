<?php

namespace Modules\Admin\Services\Others;

use App\Models\SmsTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Repositories\Others\SmsTemplateRepository;

class SmsTemplateService
{
    public function __construct(
        protected SmsTemplateRepository $repository
    ) {}

    /**
     * Get paginated SMS templates formatted for admin listing.
     */
    public function getPaginatedTemplates(
        int $limit = 25,
        ?string $search = null,
        string $sortColumn = 'template_id',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        $paginated = $this->repository->getAll(
            $limit,
            $search,
            $sortColumn,
            $sortDirection
        );

        $paginated->getCollection()->transform(function ($item) {
            $english = $item->descriptions->firstWhere('language_id', 1);
            $punjabi = $item->descriptions->firstWhere('language_id', 2);

            return [
                'id'          => $item->public_id ?? (string) $item->template_id,
                'public_id'   => $item->public_id,
                'template_id' => $item->template_id,
                'templateid'  => $item->templateid,
                'name'        => $item->name,
                'message_en'  => $english?->message ?? '',
                'message_pb'  => $punjabi?->message ?? '',
                'status'      => (bool) $item->status,
                'created_at'  => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $paginated;
    }

    /**
     * Get single SMS template details.
     */
    public function getTemplateById(int|string $id): ?array
    {
        $template = $this->repository->findById($id);
        if (!$template) {
            return null;
        }

        $english = $template->descriptions->firstWhere('language_id', 1);
        $punjabi = $template->descriptions->firstWhere('language_id', 2);

        return [
            'id'          => $template->public_id ?? (string) $template->template_id,
            'public_id'   => $template->public_id,
            'template_id' => $template->template_id,
            'templateid'  => $template->templateid,
            'name'        => $template->name,
            'message_en'  => $english?->message ?? '',
            'message_pb'  => $punjabi?->message ?? '',
            'status'      => $template->status ? 1 : 0,
            'created_at'  => $template->created_at ? $template->created_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Create SMS template with English and Punjabi messages.
     */
    public function createTemplate(array $data): SmsTemplate
    {
        return DB::transaction(function () use ($data) {
            $template = $this->repository->create([
                'name'       => $data['name'],
                'templateid' => $data['templateid'],
                'status'     => (bool) ($data['status'] ?? true),
            ]);

            if (array_key_exists('message_en', $data) && $data['message_en'] !== null && $data['message_en'] !== '') {
                $this->repository->updateOrCreateDescription($template->template_id, 1, $data['message_en']);
            }

            if (array_key_exists('message_pb', $data) && $data['message_pb'] !== null && $data['message_pb'] !== '') {
                $this->repository->updateOrCreateDescription($template->template_id, 2, $data['message_pb']);
            }

            return $template;
        });
    }

    /**
     * Update SMS template and its descriptions.
     */
    public function updateTemplate(int|string $id, array $data): ?SmsTemplate
    {
        $template = $this->repository->findById($id);
        if (!$template) {
            return null;
        }

        return DB::transaction(function () use ($template, $data) {
            $updateData = [];

            if (array_key_exists('name', $data)) {
                $updateData['name'] = $data['name'];
            }
            if (array_key_exists('templateid', $data)) {
                $updateData['templateid'] = $data['templateid'];
            }
            if (array_key_exists('status', $data)) {
                $updateData['status'] = $data['status'];
            }

            if (!empty($updateData)) {
                $this->repository->update($template, $updateData);
            }

            if (array_key_exists('message_en', $data)) {
                $this->repository->updateOrCreateDescription($template->template_id, 1, $data['message_en'] ?? '');
            }

            if (array_key_exists('message_pb', $data)) {
                $this->repository->updateOrCreateDescription($template->template_id, 2, $data['message_pb'] ?? '');
            }

            return $template;
        });
    }

    /**
     * Update status of an SMS template.
     */
    public function updateStatus(int|string $id, bool $status): bool
    {
        $template = $this->repository->findById($id);
        if (!$template) {
            return false;
        }

        return $this->repository->updateStatus($template, $status);
    }
}
