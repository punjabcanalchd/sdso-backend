<?php

namespace Modules\Front\Services;

use Modules\Front\Repositories\NoticeboardRepository;

class NoticeboardService
{
    protected $noticeboardRepository;

    public function __construct(
        NoticeboardRepository $noticeboardRepository
    ) {
        $this->noticeboardRepository = $noticeboardRepository;
    }

    public function getNoticeByIdAndLanguage($id, $languageId)
    {
        return $this->noticeboardRepository
            ->findByIdAndLanguage($id, $languageId);
    }
}