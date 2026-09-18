<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TranslationValue extends Model
{
    use HasFactory;

    protected $table = 'translation_values';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'key_id',
        'language_id',
        'translation',
    ];

    public function translation()
    {
        return $this->belongsTo(Translation::class, 'key_id', 'key_id');
    }
}
