<?php

namespace App\Models;
use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; // for creating log

/**
 * SliderImage
 *
 * @mixin Builder
 */
class SliderImage extends Model
{
    // use Loggable; //for creating log
     use HasFactory, HasPublicId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'slider_images';


     protected $primaryKey = 'id';

     protected $foreignKey ="slider_id";

      protected $appends = [
        'public_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slider_id',
        'image_name',
        'status',
        'link',
        'sort_order',
        'title',
        'title_pb',
        'page_id',
        'link_type',
    ];   
   
}
