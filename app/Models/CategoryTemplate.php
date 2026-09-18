<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTemplate extends Model
{
    protected $table = 'category_templates';
    protected $primaryKey = 'template_id';

    protected $fillable = [
        'name',
        'status',
        'display_on_home_page',
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
}
