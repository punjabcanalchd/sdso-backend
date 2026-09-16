<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;


class DamDailyReading extends Model
{
    use HasFactory, HasPublicId, LogsActivity;
    protected $table = 'damdailyreadings';
    protected $primaryKey = 'ddreadingcode';
    protected $fillable = [
        'damhwcode',
        'inflow',
        'outflow',      
        'waterlevel',
        'readingdate',
        'edited',  
        'user_id',
        'role_id',
        'editedverifier',  
    ];

    protected $appends = [
        'public_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function userrole()
    {
        return $this->belongsTo(UserRole::class, 'role_id', 'role_id');
    }

    public function dam()
    {
        return $this->belongsTo(DamHeadwork::class, 'damhwcode', 'damhwcode');
    }

    public function damheadworkDescription()
    {
         if(!defined('default_language')){
            define('default_language',1);
        }

        return $this->hasOne(DamHeadworkDescription::class,'damhwcode','damhwcode')->where('language_id','=',default_language);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
