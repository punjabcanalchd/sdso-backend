<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class Districts extends Model
{
    use HasFactory, HasPublicId;
    protected $primaryKey = 'district_id';

    protected $fillable = [
        'lgdstatecode',
        'lgddistcode',
        'status',
       
    ];

    protected $appends = [
        'public_id',
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

    public function state()
    {
        return $this->belongsTo(States::class, 'lgdstatecode', 'lgdstatecode');
    }
}


