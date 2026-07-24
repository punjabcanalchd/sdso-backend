<?php

/**
 * Module / Permission Tree Configuration
 *
 * NOTE: This file is NOT actively used right now.
 *
 * It is only read by ModulePermissionService::getPageActions($slug) to look up
 * a custom 'actions' array for a specific page (e.g. ['read'] for a log-only page).
 * Since NO entry here defines a custom 'actions' key, every page always falls back
 * to the default: ['create', 'read', 'update', 'delete'].
 *
 * This file will become relevant when you want to RESTRICT certain pages to fewer
 * permissions. To do so, add an 'actions' key to the relevant child entry, e.g.:
 *
 *   ['name' => 'Activity Logs', 'slug' => 'activity-logs', ..., 'actions' => ['read']],
 *
 * The SyncPermissionsCommand (permissions:sync) does NOT use this file at all;
 * it reads directly from the admin_pages table.
 *
 * Groups are ordered by their own id in admin_pages (= the parent_id of their children).
 * - 'slug'  : matches admin_pages.slug (Str::slug of name, backfilled by migration)
 * - 'link'  : raw value stored in admin_pages.link
 * - 'sort_order': matches admin_pages.sort_order
 */

return [
    'groups' => [

        // ── id=1 ─────────────────────────────────────────────────────────────
        [
            'name'       => 'User Management',
            'slug'       => 'user-management',
            'link'       => '#',
            'sort_order' => 99,
            'children'   => [
                ['name' => 'Users',                       'slug' => 'users',                       'link' => 'users',     'sort_order' => 98],
                ['name' => 'Roles',                       'slug' => 'roles',                       'link' => 'user-role', 'sort_order' => 99],
            ],
        ],

        // ── id=20 ────────────────────────────────────────────────────────────
        [
            'name'       => 'Others',
            'slug'       => 'others',
            'link'       => '#',
            'sort_order' => 99,
            'children'   => [
                ['name' => 'Pages',                      'slug' => 'pages',                      'link' => 'page',                    'sort_order' => 1],
                ['name' => 'Email Templates',            'slug' => 'email-templates',            'link' => 'email-template',          'sort_order' => 2],
                ['name' => 'SMS Templates',              'slug' => 'sms-templates',              'link' => 'sms-template',            'sort_order' => 3],
                ['name' => 'Translations',               'slug' => 'translations',               'link' => 'translation',             'sort_order' => 4],
                ['name' => 'Notice Board',               'slug' => 'notice-board',               'link' => 'noticeboard',             'sort_order' => 5],
                ['name' => 'Notice Board Categories',    'slug' => 'notice-board-categories',    'link' => 'category',                'sort_order' => 6],
                ['name' => 'Client Query',               'slug' => 'client-query',               'link' => 'client_query',            'sort_order' => 7],
                ['name' => 'Slider',                     'slug' => 'slider',                     'link' => 'slider',                  'sort_order' => 8],
                ['name' => 'Slider Image',               'slug' => 'slider-image',               'link' => 'slider-image',            'sort_order' => 8],
                ['name' => 'Media Categories',           'slug' => 'media-categories',           'link' => 'media-category',          'sort_order' => 16],
                ['name' => 'Media Galleries',            'slug' => 'media-galleries',            'link' => 'media-gallery',           'sort_order' => 17],
                ['name' => 'Sandes Templates',           'slug' => 'sandes-templates',           'link' => 'sandes-template',         'sort_order' => 51],
                ['name' => 'Notifications',              'slug' => 'notifications',              'link' => 'notifications',           'sort_order' => 88],
            ],
        ],

        // ── id=38 ────────────────────────────────────────────────────────────
        [
            'name'       => 'Home Page Management',
            'slug'       => 'home-page-management',
            'link'       => '#',
            'sort_order' => 50,
            'children'   => [
                ['name' => 'Youtube Section',   'slug' => 'youtube-section',   'link' => 'yt-section',        'sort_order' => 9],
                ['name' => 'Application Stats', 'slug' => 'application-stats', 'link' => 'application-stats', 'sort_order' => 10],
                ['name' => 'Menus',             'slug' => 'menus',             'link' => 'menus',             'sort_order' => 11],
            ],
        ],


        // ── id=55 ────────────────────────────────────────────────────────────
        [
            'name'       => 'Master Tables',
            'slug'       => 'master-tables',
            'link'       => '#',
            'sort_order' => 52,
            'children'   => [
                ['name' => 'States',                           'slug' => 'states',                           'link' => 'state',                     'sort_order' => 1],
                ['name' => 'Districts',                        'slug' => 'districts',                        'link' => 'district',                  'sort_order' => 2],
            ],
        ],


        // ── id=120 ───────────────────────────────────────────────────────────
        [
            'name'       => 'Log Management',
            'slug'       => 'log-management',
            'link'       => '#',
            'sort_order' => 53,
            'children'   => [
                ['name' => 'Activity Logs',               'slug' => 'activity-logs',               'link' => 'logs',                          'sort_order' => 42],
                ['name' => 'Sandes Log',                  'slug' => 'sandes-log',                  'link' => 'sandes-log',                    'sort_order' => 52],
                ['name' => 'Email Logs',                  'slug' => 'email-logs',                  'link' => 'email-log',                     'sort_order' => 54],
        ],

        ],

    ]
];
