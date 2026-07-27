<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class DistrictsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'dist_id';
    use Loggable; //for creating log

    
    protected $fillable = [
        'language_id',
        'lgddistcode',        
        'name',
        'description',
    ];
}
