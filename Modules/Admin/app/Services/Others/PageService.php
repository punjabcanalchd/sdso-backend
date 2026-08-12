<?php

namespace Modules\Admin\Services\Others;

use App\Models\Page;
use Modules\Admin\Repositories\Others\PageRepository;
use App\Models\ImageResizer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class PageService
{
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

        return $page;
    }

    public function createPage(array $data): Page
    {
        return DB::transaction(function () use ($data) {

            $data['slug'] = Str::slug($data['slug']);

            if (isset($data['page_banner']) && $data['page_banner'] instanceof UploadedFile) {
                $fileName = Str::uuid() . '.' . $data['page_banner']->getClientOriginalExtension();

                ImageResizer::store($data['page_banner'], 'uploads', $fileName);

                $data['page_banner'] = $fileName;
            }

            $translations = [
                'title' => $data['title'] ?? [],
                'description' => $data['description'] ?? [],
                'meta_title' => $data['meta_title'] ?? [],
                'meta_description' => $data['meta_description'] ?? [],
                'meta_keyword' => $data['meta_keyword'] ?? [],
            ];

            unset(
                $data['title'],
                $data['description'],
                $data['meta_title'],
                $data['meta_description'],
                $data['meta_keyword']
            );

            // Repository handles database operation
            $page = $this->repository->create($data);

            // Repository handles translation database operation
            $this->repository->createDescriptions($page->page_id, $translations);

            return $page;
        });
    }

    public function updatePage(Page $page, array $data): Page
    {

        $data['slug'] = Str::slug($data['slug']);

        $titles = $data['title'] ?? [];
        $descriptions = $data['description'] ?? [];
        $metaTitles = $data['meta_title'] ?? [];
        $metaDescriptions = $data['meta_description'] ?? [];
        $metaKeywords = $data['meta_keyword'] ?? [];

        if (isset($data['page_banner']) && $data['page_banner'] instanceof UploadedFile) {

            $fileName = Str::uuid() . '.' . $data['page_banner']->getClientOriginalExtension();
            ImageResizer::store($data['page_banner'], 'uploads', $fileName);
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

        $descriptions = [];

        foreach ($titles as $languageId => $title) {

            $descriptions[] = [
                'language_id' => $languageId,
                'title' => $title,
                'description' => $descriptions[$languageId] ?? null,
                'meta_title' => $metaTitles[$languageId] ?? null,
                'meta_description' => $metaDescriptions[$languageId] ?? null,
                'meta_keyword' => $metaKeywords[$languageId] ?? null,
            ];
        }

        DB::transaction(function () use ($page, $data, $descriptions) {

            $this->repository->updatePageWithDescriptions(
                $page,
                $data,
                $descriptions
            );
        });

        return $page->fresh();
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

        return [
            'public_id' => $page->public_id,
            'name_en'   => $english?->title,
            'name_pb'   => $punjabi?->title,
            'created_at'=> $page->created_at,
            'status'    => $page->status,
        ];
    }
}
