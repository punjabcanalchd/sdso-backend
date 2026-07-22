<?php

return [
    'allowed_domains' => array_filter(
        array_map(
            'trim',
            explode(',', env('ALLOWED_DOMAINS', ''))
        )
    ),
];
