<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class RoleCategory extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_default',
    ];

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
