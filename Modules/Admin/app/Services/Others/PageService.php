<?php

namespace Modules\Admin\Services\Others;

use App\Models\ImageResizer;
use App\Models\Page;
use App\Traits\HasPublicId;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Admin\Repositories\Others\PageRepository;

class PageService
{
    use HasPublicId;

    protected PageRepository $repository;

    public function __construct(PageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getPages(int $limit, ?string $search, ?string $sort_column, ?string $sort_direction)
    {
        $pages = $this->repository->getAll($limit, $search, $sort_column, $sort_direction);
        $pages->getCollection()->transform(function ($page) {
            return $this->formatPage($page);
        });

        return $pages;
    }

    public function getPageByPublicId(string $publicId)
    {
        $page = Page::findByPublicId($publicId);

        abort_if(! $page, 404, 'Page not found.');
        $page->load('descriptions');

        return $this->formatPage($page);
        // return $page;
    }

    public function createPage(array $data): Page
    {
        return DB::transaction(function () use ($data) {

            // Normalize slug
            $data['slug'] = Str::slug($data['slug']);

            // Handle page banner
            if (
                isset($data['page_banner']) &&
                $data['page_banner'] instanceof UploadedFile
            ) {
                $fileName = Str::uuid().'.'.
                    $data['page_banner']->getClientOriginalExtension();

                ImageResizer::store(
                    $data['page_banner'],
                    'uploads',
                    $fileName
                );

                $data['page_banner'] = $fileName;
            }

            // Keep multilingual fields separately
            $translations = [
                'title' => $data['title'] ?? [],
                'description' => $data['description'] ?? [],
                'meta_title' => $data['meta_title'] ?? [],
                'meta_description' => $data['meta_description'] ?? [],
                'meta_keyword' => $data['meta_keyword'] ?? [],
            ];

            // Remove only translation fields from tbl_pages data
            unset(
                $data['title'],
                $data['description'],
                $data['meta_title'],
                $data['meta_description'],
                $data['meta_keyword']
            );

            // Make sure page-level fields are present
            $data['sort_order'] = $data['sort_order'] ?? 0;
            $data['page_type'] = $data['page_type'] ?? 1;

            // Create page
            $page = $this->repository->create($data);

            // Create English/Punjabi descriptions + meta
            $this->repository->createDescriptions(
                $page->page_id,
                $translations
            );

            return $page;
        });
    }

    public function updatePage(string $publicId, array $data): Page
    {
        // Decrypt public ID
        $pageId = (int) $this->decode($publicId);

        $page = Page::where('page_id', $pageId)->firstOrFail();

        $data['slug'] = Str::slug($data['slug']);

        $titles = $data['title'] ?? [];
        $descriptions = $data['description'] ?? [];

        $metaTitles = $data['meta_title'] ?? [];
        $metaDescriptions = $data['meta_description'] ?? [];
        $metaKeywords = $data['meta_keyword'] ?? [];

        if (
            isset($data['page_banner']) &&
            $data['page_banner'] instanceof UploadedFile
        ) {
            $fileName = Str::uuid().'.'.
                $data['page_banner']->getClientOriginalExtension();

            ImageResizer::store(
                $data['page_banner'],
                'uploads',
                $fileName
            );

            $data['page_banner'] = $fileName;

        } else {
            unset($data['page_banner']);
        }

        unset(
            $data['title'],
            $data['description'],
            $data['meta_title'],
            $data['meta_description'],
            $data['meta_keyword']
        );

        // IMPORTANT: use a different variable
        $descriptionData = [];

        foreach ($titles as $languageId => $title) {

            $descriptionData[] = [
                'language_id' => $languageId,
                'title' => $title,
                'description' => $descriptions[$languageId] ?? null,
                'meta_title' => $metaTitles[$languageId] ?? null,
                'meta_description' => $metaDescriptions[$languageId] ?? null,
                'meta_keyword' => $metaKeywords[$languageId] ?? null,
            ];
        }

        DB::transaction(function () use (
            $page,
            $data,
            $descriptionData
        ) {
            $this->repository->updatePageWithDescriptions(
                $page,
                $data,
                $descriptionData
            );
        });

        return $page->fresh();
    }

    public function updateStatus(string $public_id, bool $status)
    {

        $pageId = (int) $this->decode($public_id);

        $page = Page::where('page_id', $pageId)->first();

        if (! $page) {
            return null;
        }

        $page->status = $status;
        $page->save();

        return $page;
    }

    public function deletePage(string $publicId): void
    {
        $page = $this->getPageByPublicId($publicId);
        $page->delete();
    }

    private function formatPage($page)
    {
        $english = $page->descriptions->firstWhere('language_id', 1);
        $punjabi = $page->descriptions->firstWhere('language_id', 2);

        // return [
        //     'public_id' => $page->public_id,
        //     'name_en' => $english?->title,
        //     'name_pb' => $punjabi?->title,
        //     'created_at' => $page->created_at,
        //     'status' => $page->status,
        // ];

        return [
            'public_id' => $page->public_id,

            // English
            'name_en' => $english?->title,
            'description_en' => $english?->description,
            'meta_title_en' => $english?->meta_title,
            'meta_description_en' => $english?->meta_description,
            'meta_keyword_en' => $english?->meta_keyword,

            // Punjabi
            'name_pb' => $punjabi?->title,
            'description_pb' => $punjabi?->description,
            'meta_title_pb' => $punjabi?->meta_title,
            'meta_description_pb' => $punjabi?->meta_description,
            'meta_keyword_pb' => $punjabi?->meta_keyword,

            // Page fields
            'page_type' => $page->page_type,
            'parent_id' => $page->parent_id,
            'slug' => $page->slug,
            'sort_order' => $page->sort_order,
            'external_url' => $page->external_url,
            'page_banner' => $page->page_banner,
            'show_on_header' => $page->show_on_header,
            'show_on_footer' => $page->show_on_footer,

            'created_at' => $page->created_at,
            'status' => $page->status,
        ];
    }
}
