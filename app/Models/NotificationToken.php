<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log  

use App\Models\NotificationLog;
/**
* Noticeboard template description
*
* @mixin Builder
*/

class NotificationToken extends Model
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
        'user_id',
        'token',
    ];
    
}
