<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class Districts extends Model
{
    use Loggable; //for creating log
    use HasFactory;
    protected $primaryKey = 'district_id';

    protected $fillable = [
        // 'state_id',
        'lgdstatecode',
        'lgddistcode',
        'status',
       
    ];

    /**
     * Get the description for the page.
     */
    public function description()
    {
        return $this->hasMany(DistrictsDescription::class,'lgddistcode','lgddistcode');
    }

	public function districtsDescription()
    {
         if(!defined('default_language')){
            define('default_language',1);
        }

        return $this->hasOne(DistrictsDescription::class,'lgddistcode','lgddistcode')->where('language_id','=',default_language);
    }    


    public static function getDistrictName($lgddistcode) {
        $return = 'None';
        $model = new DistrictsDescription;
        if(isset($lgddistcode)) {
            $data = $model->where('lgddistcode',$lgddistcode)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->name;
            }
        }
        return $return;
    }
}


