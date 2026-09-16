<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\NoticeboardTemplate;
use App\Models\NoticeboardTemplateDescription;
use App\Models\CategoryTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NoticeboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $search = $request->get('search');

        $query = NoticeboardTemplate::with(['descriptions', 'category'])
            ->orderBy('template_id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $paginated = $query->paginate($limit);

        $paginated->getCollection()->transform(function ($item) {
            $english = $item->descriptions->firstWhere('language_id', 1);
            $punjabi = $item->descriptions->firstWhere('language_id', 2);

            $englishData = json_decode($english?->message, true);
            $punjabiData = json_decode($punjabi?->message, true);

            $name_en = is_array($englishData) ? ($englishData['title'] ?? $item->name) : $item->name;
            $desc_en = is_array($englishData) ? ($englishData['description'] ?? '') : ($english?->message ?? '');

            $name_pb = is_array($punjabiData) ? ($punjabiData['title'] ?? '') : ($punjabi?->message ?? '');
            $desc_pb = is_array($punjabiData) ? ($punjabiData['description'] ?? '') : '';

            return [
                'id' => $item->template_id,
                'name_en' => $name_en,
                'description_en' => $desc_en,
                'name_pb' => $name_pb,
                'description_pb' => $desc_pb,
                'category_id' => $item->category_name,
                'category_name' => $item->category?->name ?? 'N/A',
                'publish_date' => $item->publish_date,
                'upload_notice' => $item->upload_notice,
                'status' => $item->status ? 1 : 0,
                'created_at' => $item->created_at?->format('Y-m-d H:i:s'),
            ];
        });


        return $this->paginatedResponse($paginated, 'Notice board list fetched successfully.');
    }

    public function getCategories()
    {
        $categories = CategoryTemplate::where('status', true)
            ->get(['template_id as value', 'name as label']);

        return $this->successResponse($categories, 'Categories fetched successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'category_id' => 'required',
            'publish_date' => 'required',
        ]);

        $fileName = null;
        if ($request->hasFile('notice_file')) {
            $file = $request->file('notice_file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $fileName);
        }

        $notice = NoticeboardTemplate::create([
            'name' => $request->input('name_en'),
            'category_name' => $request->input('category_id'),
            'publish_date' => $request->input('publish_date'),
            'upload_notice' => $fileName,
            'status' => $request->boolean('status', true),
        ]);

             $englishMessage = json_encode([
            'title' => $request->input('name_en', ''),
            'description' => $request->input('description_en', ''),
        ]);
        NoticeboardTemplateDescription::create([
            'template_id' => $notice->template_id,
            'language_id' => 1,
            'message' => $englishMessage,
        ]);
        if ($request->filled('name_pb') || $request->filled('description_pb')) {
            $punjabiMessage = json_encode([
                'title' => $request->input('name_pb', ''),
                'description' => $request->input('description_pb', ''),
            ]);
            NoticeboardTemplateDescription::create([
                'template_id' => $notice->template_id,
                'language_id' => 2,
                'message' => $punjabiMessage,
            ]);
        }


        return $this->successResponse($notice, 'Notice created successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $notice = NoticeboardTemplate::findOrFail($id);

        $request->validate([
            'name_en' => 'required|string',
            'category_id' => 'required',
        ]);

        $updateData = [
            'name' => $request->input('name_en'),
            'category_name' => $request->input('category_id'),
            'publish_date' => $request->input('publish_date', $notice->publish_date),
            'status' => $request->boolean('status', $notice->status),
        ];

        if ($request->hasFile('notice_file')) {
            $file = $request->file('notice_file');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $fileName);
            $updateData['upload_notice'] = $fileName;
        }

        $notice->update($updateData);

               $englishMessage = json_encode([
            'title' => $request->input('name_en', ''),
            'description' => $request->input('description_en', ''),
        ]);

        NoticeboardTemplateDescription::updateOrCreate(
            ['template_id' => $notice->template_id, 'language_id' => 1],
            ['message' => $englishMessage]
        );

        if ($request->filled('name_pb') || $request->filled('description_pb')) {
            $punjabiMessage = json_encode([
                'title' => $request->input('name_pb', ''),
                'description' => $request->input('description_pb', ''),
            ]);

            NoticeboardTemplateDescription::updateOrCreate(
                ['template_id' => $notice->template_id, 'language_id' => 2],
                ['message' => $punjabiMessage]
            );
        }



        return $this->successResponse($notice, 'Notice updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $notice = NoticeboardTemplate::findOrFail($id);
        $notice->status = $request->boolean('status');
        $notice->save();

        return $this->successResponse(['status' => $notice->status], 'Status updated successfully.');
    }

    public function destroy($id)
    {
        $notice = NoticeboardTemplate::findOrFail($id);
        $notice->descriptions()->delete();
        $notice->delete();

        return $this->successResponse(null, 'Notice deleted successfully.');
    }
}
