<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class States extends Model
{    
    use HasFactory, HasPublicId;    
    protected $primaryKey = 'state_id';      
    protected $fillable = [
    'lgdstatecode', 
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
        return $this->hasMany(StatesDescription::class,'lgdstatecode','lgdstatecode');
    }

    
    public function statesDescription()
    {
        if(!defined('default_language')){
            define('default_language',1);
        }
    return $this->hasOne(StatesDescription::class,'lgdstatecode','lgdstatecode')->where('language_id','=',default_language);
    }


    public static function getStateName($lgdstatecode) {
        $return = 'None';
        $model = new StatesDescription;
        if(isset($lgdstatecode)) {
            $data = $model->where('lgdstatecode',$lgdstatecode)->first();
            if(isset($data) && !empty($data)) {
            $return = $data->name;
            }
        }
        return $return;
    }

    public function stateDescription()
    {
        if (!defined('default_language')) {
            define('default_language', 1);
        }

        return $this->hasOne(
            StatesDescription::class,
            'lgdstatecode',
            'lgdstatecode'
        )->where('language_id', default_language);
    }
}
