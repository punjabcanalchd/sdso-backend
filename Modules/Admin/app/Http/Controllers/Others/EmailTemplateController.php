<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $search = $request->get('search');
        $sortColumn = $request->get('sort_column', 'template_id');
        $sortDirection = $request->get('sort_direction', 'desc');

        $query = EmailTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('subject', 'ilike', "%{$search}%")
                         ->orWhere('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'created_at', 'template_id'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, strtolower($sortDirection) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('template_id', 'desc');
        }

        $paginated = $query->paginate($limit);

        $paginated->getCollection()->transform(function ($item) {
            $english = $item->descriptions->firstWhere('language_id', 1);
            $punjabi = $item->descriptions->firstWhere('language_id', 2);

            return [
                'id'          => $item->public_id ?? (string) $item->template_id,
                'template_id' => $item->template_id,
                'name'        => $item->name,
                'subject_en'  => $english?->subject ?? 'N/A',
                'subject_pb'  => $punjabi?->subject ?? '',
                'message_en'  => $english?->message ?? '',
                'message_pb'  => $punjabi?->message ?? '',
                'status'      => (bool) $item->status,
                'created_at'  => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $this->paginatedResponse($paginated, 'Email templates fetched successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $template = EmailTemplate::findByPublicId($id) ?? EmailTemplate::find($id);

        if (!$template) {
            return $this->errorResponse('Email template not found.', 404);
        }

        $template->status = (bool) $request->input('status', false);
        $template->save();

        return $this->successResponse($template, 'Status updated successfully.');
    }
}
