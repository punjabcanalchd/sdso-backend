<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubDivisionsDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'sdd_id';
    protected $connection = 'pgsql'; // Primary database connection
    protected $table = 'subdivisions_descriptions';

    protected $fillable = [
        'language_id',
        'division_id',
        'subdivision_id',
        'name',
        'description',
    ];
}
