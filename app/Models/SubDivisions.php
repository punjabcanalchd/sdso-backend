<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class subdivisions extends Model
{
    use HasPublicId;
    use HasFactory;
    protected $primaryKey = 'subdivision_id';
    protected $table = ('subdivisions');
    protected $fillable = [
    'division_id',       
    'status',
    ];

    protected $appends = [
        'public_id',
    ];
    /**
    * Get the description for the page.
    */
    public function description()
    {
        return $this->hasMany(SubDivisionsDescription::class,'subdivision_id','subdivision_id');
    }
    public function subdivisionDescription()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
    return $this->hasOne(SubDivisionsDescription::class,'subdivision_id','subdivision_id')->where('language_id','=',default_language);
    }

    public function division()
    {
        return $this->belongsTo(Divisions::class, 'division_id', 'division_id');
    }
    
    
}
