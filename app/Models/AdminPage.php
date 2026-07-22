<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasPublicId;

class AdminPage extends Model
{
    use HasPublicId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_pages';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */    public $timestamps = false; // Assuming no timestamps based on the initial schema provided, adjust if needed

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'link',
        'sort_order',
        'status',
        'on_top',
        'main_module',
        'guard_name',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 1,
        'guard_name' => 'api',
    ];

    protected $appends = [
        'public_id'
    ];


    /**
     * Get the parent page.
     */
    public function parent()
    {
        return $this->belongsTo(AdminPage::class, 'parent_id');
    }

    /**
     * Get the child pages.
     */
    public function children()
    {
        return $this->hasMany(AdminPage::class, 'parent_id')->orderBy('sort_order');
    }
}
