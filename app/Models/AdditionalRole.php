<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class AdditionalRole extends Model
{
    protected $table = 'additional_roles';

    protected $fillable = [
        'role_id',
        'user_id',
        'status',
        'officecode',
    ];

    /**
     * The "booted" method of the model.
     * Applies a global scope to exclude rows where status = 0.
     */
    protected static function booted()
    {
        static::addGlobalScope('activeOnly', function (Builder $builder) {
            $builder->where('status', 1);
        });
    }

    /**
     * Get the user that owns this additional role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role associated with this additional role.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
