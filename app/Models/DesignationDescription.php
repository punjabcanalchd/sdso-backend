<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignationDescription extends Model
{  
    protected $table = 'designations_descriptions';
    use HasFactory;
    protected $primaryKey = 'desigdesc_id';
    protected $fillable = [
        'language_id',
        'desigcode',
        'designation',
        'description',
    ];
}
