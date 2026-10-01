<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\ImageResizer;
use App\Models\MediaCategoryTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaGalleryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $categoryId = $request->get('category_id');

        $query = DB::table('media_gallery_templates');

        if (!empty($categoryId)) {
            if (!is_numeric($categoryId)) {
                $cat = DB::table('media_category_templates')->where('public_id', $categoryId)->first();
                if ($cat) {
                    $categoryId = (string) $cat->mediacat_id;
                }
            }
            $query->where('category_name', (string) $categoryId);
        }

        $items = $query->orderBy('mediagal_id', 'desc')->get();

        $baseUrl = config('app.url', 'http://localhost:8000');

        $formatted = $items->map(function ($item) use ($baseUrl) {
            $imgName = $item->select_img;
            $imgUrl = '';
            if (!empty($imgName)) {
                $imgUrl = str_starts_with($imgName, 'http')
                    ? $imgName
                    : rtrim($baseUrl, '/') . '/uploads/' . ltrim($imgName, '/');
            }

            return [
                'id'             => $item->mediagal_id,
                'mediagal_id'    => $item->mediagal_id,
                'category_name'  => $item->category_name,
                'title_en'       => $item->title_en ?? '',
                'title_pb'       => $item->title_pb ?? '',
                'select_img'     => $item->select_img,
                'image_url'      => $imgUrl,
                'status'         => (bool) $item->status,
                'created_at'     => $item->created_at,
            ];
        });

        return $this->successResponse($formatted, 'Gallery items fetched successfully.');
    }

    public function store(Request $request)
    {
        $categoryId = $request->input('category_id');
        if (empty($categoryId)) {
            return $this->errorResponse('Category ID is required.', 422);
        }

        if (!is_numeric($categoryId)) {
            $cat = DB::table('media_category_templates')->where('public_id', $categoryId)->first();
            if ($cat) {
                $categoryId = (string) $cat->mediacat_id;
            }
        }

        $createdItems = [];

        DB::transaction(function () use ($request, $categoryId, &$createdItems) {
            $itemsData = $request->input('items', []);
            $itemsFiles = $request->file('items', []);

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
                        $createdItems[] = $this->createGalleryRecord($categoryId, $file, $titleEn, $titlePb);
                    }
                }
            } else {
                // Check direct single upload or multiple files
                $files = $request->file('files');
                if (is_array($files)) {
                    $titlesEn = (array) $request->input('titles_en', []);
                    $titlesPb = (array) $request->input('titles_pb', []);

                    foreach ($files as $i => $file) {
                        $titleEn = $titlesEn[$i] ?? $request->input('title_en', '');
                        $titlePb = $titlesPb[$i] ?? $request->input('title_pb', $titleEn);
                        $createdItems[] = $this->createGalleryRecord($categoryId, $file, $titleEn, $titlePb);
                    }
                } elseif ($request->hasFile('file') || $request->hasFile('upload_file')) {
                    $file = $request->file('file') ?? $request->file('upload_file');
                    $titleEn = $request->input('title_en', '');
                    $titlePb = $request->input('title_pb', $titleEn);
                    $createdItems[] = $this->createGalleryRecord($categoryId, $file, $titleEn, $titlePb);
                }
            }
        });

        if (empty($createdItems)) {
            return $this->errorResponse('No valid images were uploaded.', 422);
        }

        return $this->successResponse($createdItems, 'Media gallery image(s) added successfully.', 201);
    }

    public function update(Request $request, $id)
    {
        $item = DB::table('media_gallery_templates')->where('mediagal_id', $id)->first();
        if (!$item) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        $titleEn = $request->input('title_en', $item->title_en);
        $titlePb = $request->input('title_pb', $item->title_pb);

        DB::table('media_gallery_templates')
            ->where('mediagal_id', $id)
            ->update([
                'title_en'   => $titleEn,
                'title_pb'   => $titlePb,
                'updated_at' => now(),
            ]);

        DB::table('media_gallery_template_descriptions')
            ->where('mediagal_id', $id)
            ->where('language_id', 1)
            ->update([
                'message'    => $titleEn,
                'updated_at' => now(),
            ]);

        DB::table('media_gallery_template_descriptions')
            ->where('mediagal_id', $id)
            ->where('language_id', 2)
            ->update([
                'message'    => $titlePb,
                'updated_at' => now(),
            ]);

        return $this->successResponse(null, 'Gallery item updated successfully.');
    }

    public function destroy($id)
    {
        $item = DB::table('media_gallery_templates')->where('mediagal_id', $id)->first();
        if (!$item) {
            return $this->errorResponse('Gallery item not found.', 404);
        }

        // Delete from descriptions and gallery table
        DB::table('media_gallery_template_descriptions')->where('mediagal_id', $id)->delete();
        DB::table('media_gallery_templates')->where('mediagal_id', $id)->delete();

        // Remove physical file if it exists
        if (!empty($item->select_img)) {
            try {
                $filePath = public_path('uploads/' . $item->select_img);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            } catch (\Throwable $e) {}
        }

        return $this->successResponse(null, 'Gallery item deleted successfully.');
    }

    private function createGalleryRecord(string $categoryId, $file, string $titleEn, string $titlePb): array
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        ImageResizer::store($file, 'uploads', $fileName);

        $galId = DB::table('media_gallery_templates')->insertGetId([
            'category_name'  => (string) $categoryId,
            'status'         => true,
            'title_en'       => $titleEn,
            'title_pb'       => $titlePb,
            'select_img'     => $fileName,
            'select_video'   => null,
            'select_img_vid' => '0',
            'created_at'     => now(),
            'updated_at'     => now(),
        ], 'mediagal_id');

        DB::table('media_gallery_template_descriptions')->insert([
            [
                'mediagal_id' => $galId,
                'language_id' => 1,
                'message'     => $titleEn,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'mediagal_id' => $galId,
                'language_id' => 2,
                'message'     => $titlePb,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        return [
            'mediagal_id' => $galId,
            'select_img'  => $fileName,
            'title_en'    => $titleEn,
            'title_pb'    => $titlePb,
        ];
    }
}
