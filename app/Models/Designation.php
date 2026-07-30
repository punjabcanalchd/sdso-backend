<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class Designation extends Model
{
    use HasFactory, HasPublicId;
    protected $primaryKey = 'desigcode';      
    protected $fillable = [         
    'desigsenioritylevel',
    'status',
    ];
    
    public function description()
    {
        return $this->hasMany(DesignationDescription::class,'desigcode','desigcode');
    }
    
    
    public function designationDescription()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
    return $this->hasOne(DesignationDescription::class,'desigcode','desigcode')->where('language_id','=',default_language);
    }
    
    
    public static function getDesignation($desigcode) {
        $return = 'None';
        $model = new DesignationDescription;
        if(isset($desigcode)) {
            $data = $model->where('desigcode',$desigcode)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->designation;
            }
        }
        return $return;
    }
}
