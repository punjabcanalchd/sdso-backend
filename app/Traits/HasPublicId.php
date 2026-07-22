<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;


trait HasPublicId
{
    /**
     * Public encrypted identifier
     */
    public function getPublicIdAttribute(): string
    {
        $primaryKey = $this->getKeyName();

        return urlencode(
            Crypt::encryptString(
                (string) $this->{$primaryKey}
            )
        );
    }

    /**
     * Find model by encrypted public ID
     * Returns null if the public_id is invalid or tampered.
     */
    public static function findByPublicId(
        string $publicId
    ): ?self {

        try {
            $id = Crypt::decryptString(urldecode($publicId));
        } catch (DecryptException) {
            return null;
        }

        return static::find($id);
    }
}