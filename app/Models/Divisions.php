<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class Divisions extends Model
{
    use Loggable; //for creating log
    use HasFactory;
    protected $primaryKey = 'division_id';

    protected $fillable = [
        'circle_id',
        'status',
       
    ];

    /**
     * Get the description for the page.
     */
    public function description()
    {
        return $this->hasMany(DivisionsDescription::class,'division_id','division_id');
    }

	public function divisionDescription()
    {
         if(!defined('default_language')){
            define('default_language',1);
        }

        return $this->hasOne(DivisionsDescription::class,'division_id','division_id')->where('language_id','=',default_language);
    }



    public static function getdivisionName($division_id) {
        $return = 'None';
        $model = new DivisionsDescription;
        if(isset($division_id)) {
            $data = $model->where('division_id',$division_id)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->name;
            }
        }
        return $return;
    }
}

