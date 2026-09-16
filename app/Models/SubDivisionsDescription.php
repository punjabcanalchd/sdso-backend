<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class SubDivisionsDescription extends Model
{
    use HasFactory, LogsActivity;
    protected $primaryKey = 'sdd_id';
    protected $connection = 'pgsql'; // Primary database connection
    protected $table = 'subdivisions_descriptions';

    protected $fillable = [
        'language_id',
        'division_id',
        'subdivision_id',
        'name',
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
