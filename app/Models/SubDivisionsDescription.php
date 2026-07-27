<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log

class SubDivisionsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'sdd_id';
    protected $connection = 'pgsql'; // Primary database connection
    protected $table = 'subdivisions_descriptions';
    use Loggable; //for creating log

    protected $fillable = [
        'language_id',
        'division_id',
        'subdivision_id',
        'name',
        'description',
    ];
}
