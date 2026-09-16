<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DamHeadworkDescription extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'damheadwork_descriptions';
    protected $primaryKey = 'damhwdesc_id';
    
    protected $fillable = [
        'language_id',
        'damhwcode',
        'damhwname',
        'description',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
   
}
