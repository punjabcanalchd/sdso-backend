<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeboardTemplate extends Model
{
    protected $table = 'noticeboard_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'name',
        'status',
        'category_name',
        'publish_date',
        'upload_notice',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function descriptions(): HasMany
    {
        return $this->hasMany(NoticeboardTemplateDescription::class, 'template_id', 'template_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryTemplate::class, 'category_name', 'template_id');
    }
}
