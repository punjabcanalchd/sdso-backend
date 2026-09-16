<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeboardTemplateDescription extends Model
{
    protected $table = 'noticeboard_template_descriptions';
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'language_id',
        'message',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(NoticeboardTemplate::class, 'template_id', 'template_id');
    }
}
