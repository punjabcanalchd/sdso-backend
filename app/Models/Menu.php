<?php

namespace App\Models;

use App\Traits\HasPublicId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasPublicId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'menu_id';

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
        'link_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'link_type' => 'integer',
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
     * Get the parent menu.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id', 'menu_id');
    }

    /**
     * Get the child menus.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id', 'menu_id')
            ->where('status', true)
            ->orderBy('sort_order');
    }

    /**
     * Get the descriptions (translations) for this menu.
     */
    public function descriptions(): HasMany
    {
        return $this->hasMany(MenuDescription::class, 'menu_id', 'menu_id');
    }

    /**
     * Get the English description.
     */
    public function englishDescription()
    {
        return $this->hasOne(MenuDescription::class, 'menu_id', 'menu_id')->where('language_id', 1);
    }

    /**
     * Get the Punjabi description.
     */
    public function punjabiDescription()
    {
        return $this->hasOne(MenuDescription::class, 'menu_id', 'menu_id')->where('language_id', 2);
    }
}
