<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Yungts97\LaravelUserActivityLog\Traits\Loggable; // for creating log

class Slider extends Model
{
    use HasFactory;
    // use Loggable;

    protected $primaryKey = 'slider_id';

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Get slider images.
     */
    public function images()
    {
        return $this->hasMany(
            SliderImage::class,
            'slider_id',
            'slider_id'
        );
    }
}
