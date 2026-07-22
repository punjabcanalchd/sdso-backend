<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; //for creating log  
use App\Models\NotificationToken;

/**
* Noticeboard template description
*
* @mixin Builder
*/

class NotificationLog extends Model
{
    //use Loggable; //for creating log
    use HasFactory;
  

    /**
    * The attributes that are mass assignable.
    *
    * @var array<int, string>
    */
    protected $fillable = [
        'user_id',
        'notification_text',
    ];
	
}
