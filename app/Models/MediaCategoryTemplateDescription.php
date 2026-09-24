<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class MediaCategoryTemplateDescription extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'media_category_template_descriptions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'mediacat_id',
        'language_id',
        'message',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
