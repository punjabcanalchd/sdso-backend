<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class OfficeDescription extends Model
{
    use HasFactory;
    protected $table = 'office_descriptions';
    protected $primaryKey = 'officedesc_id';
    protected $fillable = [
        'language_id',
        'officecode',
        'officename',        
        'officeaddress',
    ];
}
