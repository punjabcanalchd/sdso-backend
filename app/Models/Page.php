<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasPublicId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'page_id',
        'external_link',
        'sort_order',
        'status',
        'show_on_footer',
        'show_on_header',
        'page_type',
        'page_banner',

    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',

    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => true,
    ];

    protected $appends = [
        'public_id',
    ];

    /**
     * Get the parent page.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id', 'page_id');
    }

    /**
     * Get the child pages.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id', 'page_id')->orderBy('sort_order');
    }

    /**
     * Get the descriptions (translations) for this menu.
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(PageDescription::class, 'page_id', 'page_id');
    }

    /**
     * Get the English description.
     */
    public function englishDescription()
    {
        return $this->hasOne(PageDescription::class, 'page_id', 'page_id')->where('language_id', 1);
    }

    /**
     * Get the Punjabi description.
     */
    public function punjabiDescription()
    {
        return $this->hasOne(PageDescription::class, 'page_id', 'page_id')->where('language_id', 2);
    }
}
