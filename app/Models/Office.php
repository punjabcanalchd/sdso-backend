<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;


class Office extends Model
{  
    use HasFactory, HasPublicId;
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

    protected $appends = [
        'public_id',
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

    public function state()
    {
        return $this->belongsTo(States::class, 'lgdstatecode', 'lgdstatecode');
    }

    public function district()
    {
        return $this->belongsTo(Districts::class, 'lgddistcode', 'lgddistcode');
    }

    public function circle()
    {
        return $this->belongsTo(Circles::class, 'circle_id', 'circle_id');
    }

    public function division()
    {
        return $this->belongsTo(Divisions::class, 'division_id', 'division_id');
    }

    public function subdivision()
    {
        return $this->belongsTo(SubDivisions::class, 'subdivision_id', 'subdivision_id');
    }

    public function officeHierarchy()
    {
        return $this->belongsTo(OfficeHierarchies::class, 'officelevelcode', 'officelevelcode');
    }

}
