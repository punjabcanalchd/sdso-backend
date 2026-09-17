<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class Slider extends Model
{
    use HasFactory, HasPublicId;

    protected $primaryKey = 'slider_id';

    protected $fillable = [
        'name',
        'status',
    ];
   
    protected $appends = [
        'public_id',
    ];

    public function images()
    {
        return $this->hasMany(
            SliderImage::class,
            'slider_id',
            'slider_id'
        );
    }
}