<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log  

/**
* SMS template description
*
* @mixin Builder
*/

class SmsTemplateDescription extends Model
{
    //use Loggable; //for creating log
    use HasFactory;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_id',
        'language_id',
        'message',
    ];
}
