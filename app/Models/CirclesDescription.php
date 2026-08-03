<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CirclesDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'cd_id';
    
    protected $fillable = [
        'language_id',
        'circle_id',
        'name',
        'description',
    ];


    

}
