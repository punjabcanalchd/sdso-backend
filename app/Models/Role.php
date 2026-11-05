<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Support\Facades\Crypt;
use Hashids\Hashids;

class Role extends SpatieRole
{
    use LogsActivity;

    protected $appends = [
        'public_id',
    ];

    public function getPublicIdAttribute(): string
    {
        return urlencode(Crypt::encryptString((string) $this->id));
    }

    /**
     * Resolve Role using public ID (supports Crypt encryption, Hashids, or numeric ID).
     */
    public static function findByPublicId(string|int $publicId): ?self
    {
        if (is_numeric($publicId)) {
            return static::find((int) $publicId);
        }

        // 1. Try Laravel Crypt decrypt (matches RoleService::encryptRoleId)
        try {
            $id = Crypt::decryptString(urldecode($publicId));
            if (is_numeric($id)) {
                return static::find((int) $id);
            }
        } catch (\Throwable $e) {
            // Not a crypt string, proceed
        }

        // 2. Try Hashids decode (if standard HasPublicId was used)
        try {
            $hashids = new Hashids(config('app.key'), 20);
            $decoded = $hashids->decode($publicId);
            if (!empty($decoded)) {
                return static::find($decoded[0]);
            }
        } catch (\Throwable $e) {
            // Not a hashid
        }

        return null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoleCategory::class, 'role_category_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }
}
