<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class Circles extends Model
{
    use HasPublicId;
    use HasFactory;
    protected $primaryKey = 'circle_id';
    protected $fillable = [
        'lgdstatecode',
        'status',      
    ];

    protected $appends = [
        'public_id',
    ];


    public function description()
    {
        return $this->hasMany(CirclesDescription::class,'circle_id','circle_id');
    }
    
    public function circleDescription()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }

        return $this->hasOne(CirclesDescription::class,'circle_id','circle_id')->where('language_id','=',default_language);
    }

    public static function getCircleName($circle_id) {
      
        $return = 'None';
        $model = new CirclesDescription;
        if(isset($circle_id)) {
            $data = $model->where('circle_id',$circle_id)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->name;
            }
        }
        return $return;
    }

    public function state()
    {
        return $this->belongsTo(States::class, 'lgdstatecode', 'lgdstatecode');
    }
}
