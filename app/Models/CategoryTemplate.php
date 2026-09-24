<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use App\Traits\HasPublicId;

class CategoryTemplate extends Model
{
    use HasFactory, LogsActivity, HasPublicId;

    protected $table = 'category_templates';
    protected $primaryKey = 'template_id';

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

    /**
     * Get the description for the noticeboard category templates.
     */
    public function descriptions()
    {
        return $this->hasMany(CategoryTemplateDescription::class,'template_id','template_id');
    }

    /**
     * Get the description or the noticeboard category templates.
     */
    public function description()
    {
        return $this->hasOne(CategoryTemplateDescription::class,'template_id','template_id')->where('language_id',default_language);
    }

    /**
     * Get the description or the noticeboard category name.
     */
	public static function getCategoryName($template_id) {
        $return = 'None';
        $model = new CategoryTemplate;
        if(isset($template_id)) {
            $data = $model->where('template_id',$template_id)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->name;
            }
        }
        return $return;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
