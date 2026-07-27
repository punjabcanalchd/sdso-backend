<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 


class Office extends Model
{  
    use HasFactory;
    protected $primaryKey = 'officecode';      
    protected $fillable = [         
    'lgdstatecode',
    'lgddistcode',
    'officelevelcode',
    'email',
    'phonelandline',
    'mobilenumber',
    'pincode',
    'status',
    'circle_id',
    'division_id',
    'subdivision_id',

    ];
    
    public function description()
    {
        return $this->hasMany(OfficeDescription::class,'officecode','officecode');
    }
    
    
    public function officeDescription()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
    return $this->hasOne(OfficeDescription::class,'officecode','officecode')->where('language_id','=',default_language);
    }
    

    
public static function getOfficeName($officecode) {
    $return = 'None';
    $model = new OfficeDescription;
    if(isset($officecode)) {
        $data = $model->where('officecode',$officecode)->first();
        if(isset($data) && !empty($data)) {
        $return = $data->officename;
        }
    }
    return $return;
}


}
