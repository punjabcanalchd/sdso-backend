<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasFactory;

    protected $table = 'translations';
    protected $primaryKey = 'key_id';
    public $timestamps = false;

    protected $fillable = [
        'key_id',
        'group',
        'translation_key',
    ];

    public function values()
    {
        return $this->hasMany(TranslationValue::class, 'key_id', 'key_id');
    }

    public function valueEn()
    {
        return $this->hasOne(TranslationValue::class, 'key_id', 'key_id')->where('language_id', 1);
    }

    public function valuePb()
    {
        return $this->hasOne(TranslationValue::class, 'key_id', 'key_id')->where('language_id', 2);
    }
}
