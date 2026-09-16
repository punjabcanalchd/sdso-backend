<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DamHeadwork extends Model
{
    use HasFactory, HasPublicId, LogsActivity;
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
    protected $appends = [
        'public_id',
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

    public function district()
    {
        return $this->belongsTo(Districts::class, 'lgddistcode', 'lgddistcode');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'officecode', 'officecode');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
