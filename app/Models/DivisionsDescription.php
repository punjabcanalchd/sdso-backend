<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DivisionsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'dd_id';

    
    protected $fillable = [
        'language_id',
        'division_id',
        'name',
        'description',
    ];

    
}
