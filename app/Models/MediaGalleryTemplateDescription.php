<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MediaGalleryTemplateDescription extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'media_gallery_template_descriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'mediagal_id',
        'language_id',
        'message',
    ];

    public function template()
    {
        return $this->belongsTo(MediaGalleryTemplate::class, 'mediagal_id', 'mediagal_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
