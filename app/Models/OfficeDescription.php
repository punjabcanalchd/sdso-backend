<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeDescription extends Model
{
    protected $table = 'office_descriptions';
    protected $primaryKey = 'officedesc_id';
    protected $fillable = [
        'language_id',
        'officecode',
        'officename',        
        'officeaddress',
    ];
}
