<?php

namespace Modules\Admin\Http\Controllers\Others;

use App\Http\Controllers\Controller;
use App\Models\ImageResizer;
use App\Models\MediaCategoryTemplate;
use App\Models\MediaCategoryTemplateDescription;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', 25);
        $search = $request->get('search');
        $sortColumn = $request->get('sort_column', 'mediacat_id');
        $sortDirection = $request->get('sort_direction', 'desc');

        $query = MediaCategoryTemplate::with('descriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('descriptions', function ($dq) use ($search) {
                      $dq->where('message', 'ilike', "%{$search}%");
                  });
            });
        }

        $allowedSorts = ['name', 'status', 'display_on_home_page', 'created_at', 'mediacat_id'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, strtolower($sortDirection) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('mediacat_id', 'desc');
        }

        $paginated = $query->paginate($limit);

        // Fetch media item counts from media gallery table
        $categoryIds = $paginated->getCollection()->map(function ($item) {
            return (string) $item->mediacat_id;
        })->toArray();

        $mediaCounts = collect();
        try {
            if (!empty($categoryIds)) {
                $mediaCounts = DB::table('media_gallery_templates')
                    ->whereIn('category_name', $categoryIds)
                    ->select('category_name', DB::raw('count(*) as count'))
                    ->groupBy('category_name')
                    ->pluck('count', 'category_name');
            }
        } catch (\Throwable $e) {
            // Silently fall back to empty counts if table query fails
        }

        $paginated->getCollection()->transform(function ($item) use ($mediaCounts) {
            $english = $item->descriptions->firstWhere('language_id', 1);
            $punjabi = $item->descriptions->firstWhere('language_id', 2);

            $en = $this->decodePayload($english?->message, $item->name);
            $pb = $this->decodePayload($punjabi?->message, '');

            $catIdStr = (string) $item->mediacat_id;
            $galleryCount = (int) ($mediaCounts->get($catIdStr, 0));
            $hasCatImg = !empty($en['image']) ? 1 : 0;
            $mediaCount = max($galleryCount, $hasCatImg);

            return [
                'id'                         => $item->public_id ?? (string) $item->mediacat_id,
                'mediacat_id'                => $item->mediacat_id,
                'name'                       => $en['title'],
                'name_en'                    => $en['title'],
                'name_pb'                    => $pb['title'],
                'description_en'             => $en['description'],
                'description_pb'             => $pb['description'],
                'category_image'             => $en['image'],
                'parent_id'                  => $en['parent_id'],
                'media_count'                => $mediaCount,
                'mediaCount'                 => $mediaCount,
                'display_on_home_page'       => (bool) $item->display_on_home_page,
                'display_on_home_page_label' => $item->display_on_home_page ? 'Yes' : 'No',
                'status'                     => (bool) $item->status,
                'status_label'               => $item->status ? 'Active' : 'Inactive',
                'created_at'                 => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
            ];
        });

        return $this->paginatedResponse($paginated, 'Media categories fetched successfully.');
    }

    public function dropdown()
    {
        $categories = MediaCategoryTemplate::orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'value' => (int) $cat->mediacat_id,
                    'label' => $cat->name,
                ];
            })
            ->toArray();

        return $this->successResponse($categories, 'Media category dropdown fetched successfully.');
    }

    public function show($id)
    {
        $category = MediaCategoryTemplate::findByPublicId($id) ?? MediaCategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        $category->load('descriptions');
        $english = $category->descriptions->firstWhere('language_id', 1);
        $punjabi = $category->descriptions->firstWhere('language_id', 2);

        $en = $this->decodePayload($english?->message, $category->name);
        $pb = $this->decodePayload($punjabi?->message, '');

        return $this->successResponse([
            'id'                   => $category->public_id ?? (string) $category->mediacat_id,
            'mediacat_id'          => $category->mediacat_id,
            'name_en'              => $en['title'],
            'name_pb'              => $pb['title'],
            'description_en'       => $en['description'],
            'description_pb'       => $pb['description'],
            'display_on_home_page' => $category->display_on_home_page ? 1 : 0,
            'status'               => $category->status ? 1 : 0,
            'category_image'       => $en['image'],
            'parent_id'            => $en['parent_id'],
        ], 'Media category fetched successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'description_en'       => ['nullable', 'string'],
            'description_pb'       => ['nullable', 'string'],
            'category_image'       => ['nullable'],
            'parent_id'            => ['nullable'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $imageFileName = null;
            if ($request->hasFile('category_image')) {
                $file = $request->file('category_image');
                $imageFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                ImageResizer::store($file, 'uploads', $imageFileName);
            } elseif (is_string($request->input('category_image'))) {
                $imageFileName = $request->input('category_image');
            }

            $parentId = !empty($validated['parent_id']) ? (int) $validated['parent_id'] : null;
            $status = $request->has('status') ? $request->boolean('status') : true;
            $displayOnHome = $request->has('display_on_home_page') ? $request->boolean('display_on_home_page') : true;

            $category = MediaCategoryTemplate::create([
                'name'                 => $validated['name_en'],
                'status'               => $status,
                'display_on_home_page' => $displayOnHome,
            ]);

            // English description
            $enPayload = [
                't'   => $validated['name_en'],
                'd'   => $request->input('description_en', ''),
                'img' => $imageFileName,
                'p'   => $parentId,
            ];

            MediaCategoryTemplateDescription::create([
                'mediacat_id' => $category->mediacat_id,
                'language_id' => 1,
                'message'     => $this->encodePayload($enPayload),
            ]);

            // Punjabi description
            $pbPayload = [
                't' => $validated['name_pb'],
                'd' => $request->input('description_pb', ''),
            ];

            MediaCategoryTemplateDescription::create([
                'mediacat_id' => $category->mediacat_id,
                'language_id' => 2,
                'message'     => $this->encodePayload($pbPayload),
            ]);

            // Also insert into media_gallery_templates if an image is uploaded
            if (!empty($imageFileName)) {
                try {
                    $galId = DB::table('media_gallery_templates')->insertGetId([
                        'category_name'  => (string) $category->mediacat_id,
                        'status'         => true,
                        'title_en'       => $validated['name_en'],
                        'title_pb'       => $validated['name_pb'],
                        'select_img'     => $imageFileName,
                        'select_video'   => null,
                        'select_img_vid' => '0',
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ], 'mediagal_id');

                    DB::table('media_gallery_template_descriptions')->insert([
                        [
                            'mediagal_id' => $galId,
                            'language_id' => 1,
                            'message'     => $validated['name_en'],
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ],
                        [
                            'mediagal_id' => $galId,
                            'language_id' => 2,
                            'message'     => $validated['name_pb'],
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ],
                    ]);
                } catch (\Throwable $e) {}
            }

            return $this->successResponse(
                $this->formatCategory($category->load('descriptions')),
                'Media category created successfully.',
                201
            );
        });
    }

    public function update(Request $request, $id)
    {
        $category = MediaCategoryTemplate::findByPublicId($id) ?? MediaCategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        $validated = $request->validate([
            'name_en'              => ['required', 'string', 'max:255'],
            'name_pb'              => ['required', 'string', 'max:255'],
            'description_en'       => ['nullable', 'string'],
            'description_pb'       => ['nullable', 'string'],
            'category_image'       => ['nullable'],
            'parent_id'            => ['nullable'],
            'display_on_home_page' => ['nullable'],
            'status'               => ['nullable'],
        ]);

        return DB::transaction(function () use ($request, $category, $validated) {
            $category->load('descriptions');
            $currentEn = $category->descriptions->firstWhere('language_id', 1);
            $existingEn = $this->decodePayload($currentEn?->message, $category->name);

            $imageFileName = $existingEn['image'];
            if ($request->hasFile('category_image')) {
                $file = $request->file('category_image');
                $imageFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                ImageResizer::store($file, 'uploads', $imageFileName);
            } elseif ($request->filled('category_image') && is_string($request->input('category_image'))) {
                $imageFileName = $request->input('category_image');
            }

            $parentId = array_key_exists('parent_id', $validated)
                ? (!empty($validated['parent_id']) ? (int) $validated['parent_id'] : null)
                : $existingEn['parent_id'];

            $status = $request->has('status') ? $request->boolean('status') : $category->status;
            $displayOnHome = $request->has('display_on_home_page') ? $request->boolean('display_on_home_page') : $category->display_on_home_page;

            $category->update([
                'name'                 => $validated['name_en'],
                'status'               => $status,
                'display_on_home_page' => $displayOnHome,
            ]);

            // English description
            $enPayload = [
                't'   => $validated['name_en'],
                'd'   => array_key_exists('description_en', $validated) ? $request->input('description_en', '') : $existingEn['description'],
                'img' => $imageFileName,
                'p'   => $parentId,
            ];

            MediaCategoryTemplateDescription::updateOrCreate(
                [
                    'mediacat_id' => $category->mediacat_id,
                    'language_id' => 1,
                ],
                [
                    'message' => $this->encodePayload($enPayload),
                ]
            );

            // Punjabi description
            $currentPb = $category->descriptions->firstWhere('language_id', 2);
            $existingPb = $this->decodePayload($currentPb?->message, '');

            $pbPayload = [
                't' => $validated['name_pb'],
                'd' => array_key_exists('description_pb', $validated) ? $request->input('description_pb', '') : $existingPb['description'],
            ];

            MediaCategoryTemplateDescription::updateOrCreate(
                [
                    'mediacat_id' => $category->mediacat_id,
                    'language_id' => 2,
                ],
                [
                    'message' => $this->encodePayload($pbPayload),
                ]
            );

            // Sync with media_gallery_templates if an image is provided
            if (!empty($imageFileName)) {
                try {
                    $existingGal = DB::table('media_gallery_templates')
                        ->where('category_name', (string) $category->mediacat_id)
                        ->first();

                    if ($existingGal) {
                        DB::table('media_gallery_templates')
                            ->where('mediagal_id', $existingGal->mediagal_id)
                            ->update([
                                'title_en'   => $validated['name_en'],
                                'title_pb'   => $validated['name_pb'],
                                'select_img' => $imageFileName,
                                'updated_at' => now(),
                            ]);
                    } else {
                        $galId = DB::table('media_gallery_templates')->insertGetId([
                            'category_name'  => (string) $category->mediacat_id,
                            'status'         => true,
                            'title_en'       => $validated['name_en'],
                            'title_pb'       => $validated['name_pb'],
                            'select_img'     => $imageFileName,
                            'select_video'   => null,
                            'select_img_vid' => '0',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ], 'mediagal_id');

                        DB::table('media_gallery_template_descriptions')->insert([
                            [
                                'mediagal_id' => $galId,
                                'language_id' => 1,
                                'message'     => $validated['name_en'],
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ],
                            [
                                'mediagal_id' => $galId,
                                'language_id' => 2,
                                'message'     => $validated['name_pb'],
                                'created_at'  => now(),
                                'updated_at'  => now(),
                            ],
                        ]);
                    }
                } catch (\Throwable $e) {}
            }

            return $this->successResponse(
                $this->formatCategory($category->fresh(['descriptions'])),
                'Media category updated successfully.'
            );
        });
    }

    public function updateStatus(Request $request, $id)
    {
        $category = MediaCategoryTemplate::findByPublicId($id) ?? MediaCategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        $category->status = (bool) $request->input('status', false);
        $category->save();

        return $this->successResponse($category, 'Status updated successfully.');
    }

    public function updateDisplayOnHome(Request $request, $id)
    {
        $category = MediaCategoryTemplate::findByPublicId($id) ?? MediaCategoryTemplate::find($id);

        if (!$category) {
            return $this->errorResponse('Media category not found.', 404);
        }

        $category->display_on_home_page = (bool) $request->input('display_on_home_page', false);
        $category->save();

        return $this->successResponse($category, 'Display on home page updated successfully.');
    }

    public function encodePayload(array $data, int $maxLength = 255): string
    {
        $clean = [];
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null) {
                $clean[$k] = $v;
            }
        }

        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        // If too long, trim description
        if (isset($clean['d']) && is_string($clean['d'])) {
            $allowedDLen = max(10, $maxLength - (strlen($json) - strlen($clean['d'])));
            $clean['d'] = mb_substr($clean['d'], 0, $allowedDLen);
        }
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        unset($clean['d']);
        $json = json_encode($clean, JSON_UNESCAPED_UNICODE);
        if (strlen($json) <= $maxLength) {
            return $json;
        }

        return mb_strcut($json, 0, $maxLength);
    }

    public function decodePayload(?string $message, string $defaultTitle = ''): array
    {
        if (empty($message)) {
            return [
                'title'       => $defaultTitle,
                'description' => '',
                'image'       => null,
                'parent_id'   => null,
            ];
        }

        if (str_starts_with(trim($message), '{')) {
            $data = json_decode($message, true);
            if (is_array($data)) {
                return [
                    'title'       => $data['t'] ?? $data['title'] ?? $data['name'] ?? $defaultTitle,
                    'description' => $data['d'] ?? $data['description'] ?? '',
                    'image'       => $data['img'] ?? $data['image'] ?? null,
                    'parent_id'   => isset($data['p']) ? (int) $data['p'] : ($data['parent_id'] ?? null),
                ];
            }
        }

        return [
            'title'       => $message ?: $defaultTitle,
            'description' => '',
            'image'       => null,
            'parent_id'   => null,
        ];
    }

    private function formatCategory(MediaCategoryTemplate $item): array
    {
        $english = $item->descriptions->firstWhere('language_id', 1);
        $punjabi = $item->descriptions->firstWhere('language_id', 2);

        $en = $this->decodePayload($english?->message, $item->name);
        $pb = $this->decodePayload($punjabi?->message, '');

        return [
            'id'                   => $item->public_id ?? (string) $item->mediacat_id,
            'mediacat_id'          => $item->mediacat_id,
            'name'                 => $en['title'],
            'name_en'              => $en['title'],
            'name_pb'              => $pb['title'],
            'description_en'       => $en['description'],
            'description_pb'       => $pb['description'],
            'category_image'       => $en['image'],
            'parent_id'            => $en['parent_id'],
            'display_on_home_page' => $item->display_on_home_page ? 1 : 0,
            'status'               => $item->status ? 1 : 0,
            'created_at'           => $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
