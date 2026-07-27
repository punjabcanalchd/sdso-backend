<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log

class DamHeadwork extends Model
{
    use Loggable; //for creating log
    use HasFactory;
    protected $table = 'damheadworks';
    protected $primaryKey = 'damhwcode';
    protected $fillable = [
        'startlat',
        'startlong',
        'lgddistcode',      
        'officecode',
        'entitycode',
        'status',
    ];
    
    public function description()
    {
        return $this->hasMany(DamHeadworkDescription::class,'damhwcode','damhwcode');
    }

    public function damheadworkDescription()
    {
         if(!defined('default_language')){
            define('default_language',1);
        }

        return $this->hasOne(DamHeadworkDescription::class,'damhwcode','damhwcode')->where('language_id','=',default_language);
    }

    public static function getDamHeadworkName($damhwcode) {
        $return = 'None';
        $model = new DamHeadworkDescription;
        if(isset($damhwcode)) {
            $data = $model->where('damhwcode',$damhwcode)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->damhwname;
            }
        }
        return $return;
    }
}
