<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log 

class OfficeHierarchiesDescription extends Model
{
    use HasFactory;
    protected $primaryKey = 'oh_id';
    protected $fillable = [
        'language_id',
        'officelevelcode',
        'officelevel',
        'description',
    ];
}
