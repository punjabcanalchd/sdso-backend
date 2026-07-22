<?php

return [

    'default_limit' => env(
        'PAGINATION_DEFAULT_LIMIT',
        10
    ),

    'max_limit' => env(
        'PAGINATION_MAX_LIMIT',
        100
    ),

];