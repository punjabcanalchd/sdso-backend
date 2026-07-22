<?php

namespace App\Validation\Rules;

use App\Validation\Patterns\RegexPatterns;
use App\Validation\Rules\StrongPasswordRule;

class CommonRules
{
    public static function name(): array
    {
        return [
            'required',
            'string',
            'min:2',
            'max:100',
            'regex:' . RegexPatterns::ALPHA
        ];
    }

    public static function punjabiName(): array
    {
        return [
            'required',
            'string',
            'min:2',
            'max:100',
            'regex:' . RegexPatterns::GURMUKHI
        ];
    }

    public static function nameOrPunjabiName(): array
    {
        return [
            'required',
            'string',
            'min:2',
            'max:100',
            'regex:' . RegexPatterns::GURMUKHI_ALPHA
        ];
    }

    public static function email(): array
    {
        return [
            'required',
            'email',
            'min:5',
            'max:255',
            'regex:' . RegexPatterns::EMAIL
        ];
    }

    public static function phone(): array
    {
        return [
            'required',
            'regex:' . RegexPatterns::PHONE_10
        ];
    }

    public static function username(): array
    {
        return [
            'required',
            'min:3',
            'max:50',
            'regex:' . RegexPatterns::ALPHA_NUM_UNDERSCORE
        ];
    }

    public static function slug(): array
    {
        return [
            'required',
            'min:3',
            'max:100',
            'regex:' . RegexPatterns::SLUG
        ];
    }

    public static function positiveInteger(): array
    {
        return [
            'required',
            'regex:' . RegexPatterns::POSITIVE_INT
        ];
    }

    public static function decimal2(): array
    {
        return [
            'required',
            'regex:' . RegexPatterns::DECIMAL_2
        ];
    }

    public static function password(): array
    {
        return [
            'required',
            'string',
            'min:8',
            'max:50',
            new StrongPasswordRule(),
        ];
    }

    public static function otp(): array
    {
        return [
            'required',
            'digits_between:4,6'
        ];
    }

    public static function futureDate(): array
    {
        return [
            'required',
            'date',
            'after_or_equal:today'
        ];
    }

    public static function pastDate(): array
    {
        return [
            'required',
            'date',
            'before:today'
        ];
    }
}