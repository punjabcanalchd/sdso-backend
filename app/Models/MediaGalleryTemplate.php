<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MediaGalleryTemplate extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'media_gallery_templates';
    protected $primaryKey = 'mediagal_id';

    protected $fillable = [
        'category_name',
        'status',
        'title_en',
        'title_pb',
        'select_img',
        'select_video',
        'select_img_vid',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function descriptions()
    {
        return $this->hasMany(MediaGalleryTemplateDescription::class, 'mediagal_id', 'mediagal_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
