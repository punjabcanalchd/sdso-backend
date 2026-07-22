<?php

namespace App\Enums;

enum ApplicationStatus: int
{
    case Draft = 1;
    case Submitted = 2;
    case Approved = 3;
    case Rejected = 4;
    case Objection = 5;
    case Revocated = 6;
    case Deactivated = 7;
    case Withdrawn = 8;

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Objection => 'Objection',
            self::Revocated => 'Revocated',
            self::Deactivated => 'Deactivated',
            self::Withdrawn => 'Withdrawn',
        };
    }
    public static function resolveLabel(?int $applicationStatus, ?int $paymentStatus = null): string {
        if ($applicationStatus === self::Submitted->value && $paymentStatus != 1) {
            return self::Draft->label();
        }
        return self::tryFrom($applicationStatus)?->label() ?? '';
    }
}
