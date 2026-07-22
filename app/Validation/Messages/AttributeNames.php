<?php

namespace App\Validation\Messages;

class AttributeNames
{
    public static function attributes(): array
    {
        return [

            'name' => 'Full Name',
            'email' => 'Email Address',
            'mobile' => 'Mobile Number',
            'username' => 'Username',
        ];
    }
}