<?php
namespace App\Enums;

enum ServiceType: int
{
    public static function gwIntimationCodes(): array
    {
        return [9, 10, 11, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27];
    }

    public static function gwAmendmentCodes(): array
    {
        return [3, 4, 5, 6, 7];
    }
    public static function gwRenewalCodes(): array
    {
        return [2];
    }
    public static function gwRevocationCodes(): array
    {
        return [8];
    }
    public static function gwFreshCodes(): array
    {
        return [1];
    }   
}