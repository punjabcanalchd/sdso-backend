<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log

class DamHeadworkDescription extends Model
{
    use Loggable; //for creating log
    use HasFactory;
    protected $table = 'damheadwork_descriptions';
    protected $primaryKey = 'damhwdesc_id';
    
    protected $fillable = [
        'language_id',
        'damhwcode',
        'damhwname',
        'description',
    ];
   
}
