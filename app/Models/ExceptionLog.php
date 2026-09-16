<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;


/**
 * Email Log
 *
 * @mixin Builder
 */
class ExceptionLog extends Model
{
    use HasFactory, HasPublicId;
	protected $fillable = [
        'message',
        'line',
        'trace',
        'url',
        'body',
        'ip',
    ];
    
    protected $appends = [
        'public_id',
    ];
}