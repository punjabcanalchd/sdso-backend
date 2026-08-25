<?php

namespace App\Traits;

use Hashids\Hashids;

trait HasPublicId
{
    protected function getHashidsInstance(): Hashids
    {
        return new Hashids(
            config('app.key'),
            20
        );
    }

    /**
     * Get public ID for current model.
     */
    public function getPublicIdAttribute(): string
    {
        return $this->getHashidsInstance()->encode(
            $this->getKey()
        );
    }

    /**
     * Encode database ID to public ID.
     */
    public function encodeKey(int|string $key): string
    {
        return $this->getHashidsInstance()->encode((int) $key);
    }

    /**
     * Decode public ID to database ID.
     */
    public function decode(string $string): int
    {
        if (is_numeric($string)) {
            return (int) $string;
        }

        $decoded = $this->getHashidsInstance()->decode($string);

        if (empty($decoded)) {
            abort(404, 'Something went wrong');
        }

        return (int) $decoded[0];
    }

    /**
     * Find model using public ID.
     */
    public static function findByPublicId(string $publicId): ?self
    {
        $instance = new static();

        $decoded = $instance->getHashidsInstance()->decode($publicId);

        if (empty($decoded)) {
            return null;
        }

        return static::find($decoded[0]);
    }
}