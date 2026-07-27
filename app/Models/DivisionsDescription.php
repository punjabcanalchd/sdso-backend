<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log  

class DivisionsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'dd_id';
    use Loggable; //for creating log

    
    protected $fillable = [
        'language_id',
        'division_id',
        'name',
        'description',
    ];

    
}
