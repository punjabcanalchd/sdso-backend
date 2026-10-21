<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuerySubject extends Model
{
    protected $table = 'grievance_form_subjects';
    protected $primaryKey = 'id';

    protected $fillable = [
        'subject',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
