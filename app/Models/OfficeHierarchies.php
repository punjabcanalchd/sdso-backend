<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class OfficeHierarchies extends Model
{
            
use HasFactory, HasPublicId;
protected $primaryKey = 'officelevelcode';      
protected $fillable = [         
'officesenioritylevel',
'status',
];

public function description()
{
    return $this->hasMany(OfficeHierarchiesDescription::class,'officelevelcode','officelevelcode');
}


public function officehierarchiesDescription()
{
    if(!defined('default_language')){
        define('default_language',1);
    }
return $this->hasOne(OfficeHierarchiesDescription::class,'officelevelcode','officelevelcode')->where('language_id','=',default_language);
}


public static function getOfficeLevel($officelevelcode) {
    $return = 'None';
    $model = new OfficeHierarchiesDescription;
    if(isset($officelevelcode)) {
        $data = $model->where('officelevelcode',$officelevelcode)->first();
        if(isset($data) && !empty($data)) {
        $return = $data->officelevel;
        }
    }
    return $return;
}

}
