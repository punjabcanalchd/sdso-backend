<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistrictsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'dist_id';

    
    protected $fillable = [
        'language_id',
        'lgddistcode',        
        'name',
        'description',
    ];
}
