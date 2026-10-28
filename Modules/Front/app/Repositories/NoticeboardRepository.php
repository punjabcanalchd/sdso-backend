<?php

namespace Modules\Front\Repositories;


use App\Models\CategoryTemplate;
use App\Models\NoticeboardTemplate;

class NoticeboardRepository
{
    public function findByIdAndLanguage($id, $languageId)
    {
        return NoticeboardTemplate::where('template_id', $id)
            ->whereHas('descriptions', function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            })
            ->with(['descriptions' => function ($query) use ($languageId) {
                $query->where('language_id', $languageId);
            }, 'category'])
            ->first();
    }
}