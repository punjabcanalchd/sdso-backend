<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Modules\Admin\Services\Others\MediaGalleryService;

class MediaGalleryController extends Controller
{
    use ApiResponse;

    public function __construct(protected MediaGalleryService $service)
    {
    }

    /**
     * Get all gallery items for a category.
     */
    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');
        $search = $request->get('search');

        $formatted = $this->service->getGalleryItems($categoryId, $search);

        return $this->successResponse($formatted, 'Gallery items fetched successfully.');
    }

    /**
     * Store one or more gallery images.
     */
    public function store(Request $request)
    {
        $categoryId = $request->input('category_id');
        if (empty($categoryId)) {
            return $this->errorResponse('Category ID is required.', 422);
        }

        $itemsData = $request->input('items', []);
        $itemsFiles = $request->file('items', []);
        $rows = [];

        if (!empty($itemsData) || !empty($itemsFiles)) {
            $count = max(is_array($itemsData) ? count($itemsData) : 0, is_array($itemsFiles) ? count($itemsFiles) : 0, 10);
            for ($index = 0; $index < $count; $index++) {
                $file = $request->file("items.{$index}.file")
                    ?? ($itemsFiles[$index]['file'] ?? null)
                    ?? $request->file("files.{$index}");

                $titleEn = $request->input("items.{$index}.title_en")
                    ?? ($itemsData[$index]['title_en'] ?? '');
                $titlePb = $request->input("items.{$index}.title_pb")
                    ?? ($itemsData[$index]['title_pb'] ?? $titleEn);

                if ($file) {
                    $rows[] = [
                        'file'     => $file,
                        'title_en' => $titleEn,
                        'title_pb' => $titlePb,
                    ];
                }
            }
        } else {
            $files = $request->file('files');
            if (is_array($files)) {
                $titlesEn = (array) $request->input('titles_en', []);
                $titlesPb = (array) $request->input('titles_pb', []);

                foreach ($files as $i => $file) {
                    $titleEn = $titlesEn[$i] ?? $request->input('title_en', '');
                    $titlePb = $titlesPb[$i] ?? $request->input('title_pb', $titleEn);
                    $rows[] = [
                        'file'     => $file,
                        'title_en' => $titleEn,
                        'title_pb' => $titlePb,
                    ];
                }
            } elseif ($request->hasFile('file') || $request->hasFile('upload_file')) {
                $file = $request->file('file') ?? $request->file('upload_file');
                $titleEn = $request->input('title_en', '');
                $titlePb = $request->input('title_pb', $titleEn);
                $rows[] = [
                    'file'     => $file,
                    'title_en' => $titleEn,
                    'title_pb' => $titlePb,
                ];
            }
        }

        if (empty($rows)) {
            return $this->errorResponse('No valid images were uploaded.', 422);
        }

        $created = $this->service->createBatch((string) $categoryId, $rows);

        return $this->successResponse($created, 'Media gallery image(s) added successfully.', 201);
    }

    /**
     * Update title translations of a gallery item.
     */
    public function update(Request $request, $id)
    {
        $titleEn = $request->input('title_en', '');
        $titlePb = $request->input('title_pb', $titleEn);

        $updated = $this->service->updateTitles((int) $id, (string) $titleEn, (string) $titlePb);

        if (!$updated) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        return $this->successResponse(null, 'Gallery item updated successfully.');
    }

    /**
     * Delete a gallery item.
     */
    public function destroy($id)
    {
        $deleted = $this->service->deleteItem((int) $id);

        if (!$deleted) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        return $this->successResponse(null, 'Gallery item deleted successfully.');
    }
}
