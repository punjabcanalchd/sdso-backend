<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class OfficeDescription extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;
    protected $table = 'office_descriptions';
    protected $primaryKey = 'officedesc_id';
    protected $fillable = [
        'language_id',
        'officecode',
        'officename',        
        'officeaddress',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
