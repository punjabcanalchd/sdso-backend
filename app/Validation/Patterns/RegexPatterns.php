<?php

namespace App\Validation\Patterns;

class RegexPatterns
{
    /* ---------- TEXT ---------- */

    public const ALPHA = '/^[A-Za-z\s]+$/';

    public const GURMUKHI = '/^[\p{Gurmukhi}\s]+$/u';

    public const GURMUKHI_ALPHA = '/^[\p{Gurmukhi}A-Za-z\s]+$/u';

    public const ALPHA_NUM = '/^[A-Za-z0-9\s]+$/';

    public const ALPHA_NUM_DASH = '/^[A-Za-z0-9\s\-]+$/';

    public const ALPHA_NUM_UNDERSCORE = '/^[A-Za-z0-9_]+$/';

    public const ALPHA_NUM_SPECIAL ='/^[A-Za-z0-9\s\'"()_.,]+$/';

    public const SLUG ='/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public const DIGITS_ONLY = '/^\d+$/';

    public const POSITIVE_INT = '/^[1-9]\d*$/';

    public const DECIMAL_2 ='/^\d+(\.\d{1,2})?$/';

    public const PHONE_10 = '/^[6-9][0-9]{9}$/';

    public const OTP ='/^\d{4,6}$/';

    public const EMAIL ='/^[a-zA-Z0-9_+-]+(?:\.[a-zA-Z0-9_+-]+)*@[a-zA-Z0-9-]+(?:\.[a-zA-Z]{2,})+$/';

    public const PAN_CARD = '/^[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}$/';

    public const DRIVING_LICENSE = '/^[A-Za-z0-9_]+$/';
    
    public const PASSPORT = '/^[A-Za-z]{3}[0-9]{7}$/';
}