<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class CirclesDescription extends Model
{
    use Loggable; //for creating log
    use HasFactory;
    protected $primaryKey = 'cd_id';
    
    protected $fillable = [
        'language_id',
        'circle_id',
        'name',
        'description',
    ];


    

}
