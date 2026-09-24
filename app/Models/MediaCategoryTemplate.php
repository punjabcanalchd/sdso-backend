<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use App\Traits\HasPublicId;

class MediaCategoryTemplate extends Model
{
    use HasFactory, LogsActivity, HasPublicId;

    protected $table = 'media_category_templates';
    protected $primaryKey = 'mediacat_id';

    protected $appends = [
        'public_id',
    ];

    protected $fillable = [
        'name',
        'status',
        'display_on_home_page',
    ];

    protected $casts = [
        'status' => 'boolean',
        'display_on_home_page' => 'boolean',
    ];

    public function descriptions()
    {
        return $this->hasMany(MediaCategoryTemplateDescription::class, 'mediacat_id', 'mediacat_id');
    }

    public function description()
    {
        if (!defined('default_language')) {
            define('default_language', 1);
        }

        return $this->hasOne(MediaCategoryTemplateDescription::class, 'mediacat_id', 'mediacat_id')->where('language_id', default_language);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
