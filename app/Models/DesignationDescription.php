<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class DesignationDescription extends Model
{  
    protected $table = 'designations_descriptions';
    use HasFactory, LogsActivity;
    protected $primaryKey = 'desigdesc_id';
    protected $fillable = [
        'language_id',
        'desigcode',
        'designation',
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
