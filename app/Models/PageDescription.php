<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageDescription extends Model
{
    protected $table = 'pages_descriptions';

    protected $primaryKey = 'description_id';

    public $timestamps = false;

    protected $fillable = [
        'page_id',
        'language_id',
        'description',
        'title',
        'meta_title',
        'meta_description',
        'meta_keyword',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id', 'page_id');
    }
}
