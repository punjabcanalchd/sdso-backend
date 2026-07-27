<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class StatesDescription extends Model
{    
    use HasFactory;
    protected $primaryKey = 'st_id';
    protected $fillable = [        
        'language_id',
        'lgdstatecode',        
        'name',
        'description',
    ];

   
   
   
}
