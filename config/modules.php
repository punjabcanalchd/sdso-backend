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
                ['name' => 'Permission Emails Dist.Wise', 'slug' => 'permission-emails-dist-wise', 'link' => 'emails',    'sort_order' => 88],
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
                ['name' => 'Administrative Contacts',    'slug' => 'administrative-contacts',    'link' => 'administrator-contacts',  'sort_order' => 7],
                ['name' => 'Slider',                     'slug' => 'slider',                     'link' => 'slider',                  'sort_order' => 8],
                ['name' => 'Slider Image',               'slug' => 'slider-image',               'link' => 'slider-image',            'sort_order' => 8],
                ['name' => 'Transfer Unit',              'slug' => 'transfer-unit',              'link' => 'transferunit',            'sort_order' => 8],
                ['name' => 'Event Manager',              'slug' => 'event-manager',              'link' => 'events',                  'sort_order' => 10],
                ['name' => 'Media Categories',           'slug' => 'media-categories',           'link' => 'media-category',          'sort_order' => 16],
                ['name' => 'Media Galleries',            'slug' => 'media-galleries',            'link' => 'media-gallery',           'sort_order' => 17],
                ['name' => 'Water Resource Assignments', 'slug' => 'water-resource-assignments', 'link' => 'water-resource-assignment','sort_order' => 20],
                ['name' => 'Offline Records',            'slug' => 'offline-records',            'link' => 'offline-records',         'sort_order' => 21],
                ['name' => 'Sandes Templates',           'slug' => 'sandes-templates',           'link' => 'sandes-template',         'sort_order' => 51],
                ['name' => 'MPS Offline',                'slug' => 'mps-offline',                'link' => 'mps-offline',             'sort_order' => 88],
                ['name' => 'Notifications',              'slug' => 'notifications',              'link' => 'notifications',           'sort_order' => 88],
                ['name' => 'Scrutiny WorkFlow',          'slug' => 'scrutiny-workflow',          'link' => 'workflow-mechanism',      'sort_order' => 88],
                ['name' => 'Holidays',                   'slug' => 'holidays',                   'link' => 'holidays',                'sort_order' => 89],
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

        // ── id=43 ────────────────────────────────────────────────────────────
        [
            'name'       => 'Application Management',
            'slug'       => 'application-management',
            'link'       => '#',
            'sort_order' => 51,
            'children'   => [
                ['name' => 'Type of Service',             'slug' => 'type-of-service',             'link' => 'type-of-service',            'sort_order' => 6],
                ['name' => 'GW Reactivation',             'slug' => 'gw-reactivation',             'link' => 'gw-reactivation',            'sort_order' => 7],
                ['name' => 'Type of Unit',                'slug' => 'type-of-unit',                'link' => 'type-of-unit',               'sort_order' => 13],
                ['name' => 'Applications With Objection', 'slug' => 'applications-with-objection', 'link' => 'applications-with-objection','sort_order' => 13],
                ['name' => 'Type of Ownership Units',     'slug' => 'type-of-ownership-units',     'link' => 'type-of-ownership-unit',     'sort_order' => 15],
                ['name' => 'Registered Units',            'slug' => 'registered-units',            'link' => 'registered-unit',            'sort_order' => 19],
                ['name' => 'Drilling Rig',                'slug' => 'drilling-rig',                'link' => 'drilling-rig',               'sort_order' => 25],
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
                ['name' => 'Sub-Districts',                    'slug' => 'sub-districts',                    'link' => 'sub-districts',             'sort_order' => 3],
                ['name' => 'Block/Assessment Areas',           'slug' => 'block-assessment-areas',           'link' => 'block-assessment-area',     'sort_order' => 4],
                ['name' => 'Urban Local Bodies',               'slug' => 'urban-local-bodies',               'link' => 'urban-local-bodies',        'sort_order' => 5],
                ['name' => 'Villages',                         'slug' => 'villages',                         'link' => 'villages',                  'sort_order' => 6],
                ['name' => 'Category(2021 Assessment Report)', 'slug' => 'category-2021-assessment-report',  'link' => 'category-assessment-report', 'sort_order' => 22],
                ['name' => 'Groundwater Status',               'slug' => 'groundwater-status',               'link' => 'groundwater-status',        'sort_order' => 23],
                ['name' => 'Water Chemical Type',              'slug' => 'water-chemical-type',              'link' => 'water-chemical-type',       'sort_order' => 30],
                ['name' => 'Type of Pump',                     'slug' => 'type-of-pump',                     'link' => 'type-of-pump',              'sort_order' => 31],
                ['name' => 'Type of Meter',                    'slug' => 'type-of-meter',                    'link' => 'type-of-meter',             'sort_order' => 32],
                ['name' => 'Source of Power',                  'slug' => 'source-of-power',                  'link' => 'source-of-power',           'sort_order' => 40],
                ['name' => 'Tenure of Permission',             'slug' => 'tenure-of-permission',             'link' => 'tenure-of-permission',      'sort_order' => 78],
                ['name' => 'Water Auditors',                   'slug' => 'water-auditors',                   'link' => 'water-auditors',            'sort_order' => 103],
            ],
        ],

        // ── id=92 ────────────────────────────────────────────────────────────
        [
            'name'       => 'Manage Fees Structure',
            'slug'       => 'manage-fees-structure',
            'link'       => '#',
            'sort_order' => 26,
            'children'   => [
                ['name' => 'Time Period For Existing Unit',                   'slug' => 'time-period-for-existing-unit',                   'link' => 'time-period',               'sort_order' => 27],
                ['name' => 'Fees Applicable for All',                        'slug' => 'fees-applicable-for-all',                        'link' => 'fee-applicable',            'sort_order' => 28],
                ['name' => 'Ground Water Extraction Charges',                 'slug' => 'ground-water-extraction-charges',                 'link' => 'gw-extraction',             'sort_order' => 29],
                ['name' => 'Rebate Water Conservation',                      'slug' => 'rebate-water-conservation',                      'link' => 'rebate-water-conservation', 'sort_order' => 33],
                ['name' => 'Additional Ground Water Charges for Conveyance', 'slug' => 'additional-ground-water-charges-for-conveyance', 'link' => 'gwconveyance-charges',      'sort_order' => 34],
                ['name' => 'Groundwater Compensation',                       'slug' => 'groundwater-compensation',                       'link' => 'gw-compensation',           'sort_order' => 35],
                ['name' => 'Non-Compliance Charges',                         'slug' => 'non-compliance-charges',                         'link' => 'non-compliance-charges',    'sort_order' => 36],
                ['name' => 'Nature of Application',                          'slug' => 'nature-of-application',                          'link' => 'nature-of-application',     'sort_order' => 38],
                ['name' => 'Fees for Tube Wells',                            'slug' => 'fees-for-tube-wells',                            'link' => 'fees-for-tubewell',         'sort_order' => 39],
            ],
        ],

        // ── id=111 ───────────────────────────────────────────────────────────
        [
            'name'       => 'Workflow Management',
            'slug'       => 'workflow-management',
            'link'       => '#',
            'sort_order' => 43,
            'children'   => [
                ['name' => 'Application Type Wise Assigned',         'slug' => 'application-type-wise-assigned',         'link' => 'application-type-wise-assigned',          'sort_order' => 44],
                ['name' => 'Service Wise Assigned',                  'slug' => 'service-wise-assigned',                  'link' => 'user-role-service-wise-assigned',         'sort_order' => 45],
                ['name' => 'Volume Wise Assigned',                   'slug' => 'volume-wise-assigned',                   'link' => 'user-role-volume-wise-assigned',          'sort_order' => 47],
                ['name' => 'Unit Type Wise Assigned',                'slug' => 'unit-type-wise-assigned',                'link' => 'unit-type-wise-assigned',                 'sort_order' => 48],
                ['name' => 'District Wise Assigned',                 'slug' => 'district-wise-assigned',                 'link' => 'user-role-district-wise-assigned',        'sort_order' => 49],
                ['name' => 'Application Assignment Method Settings', 'slug' => 'application-assignment-method-settings', 'link' => 'assignment_method_settings',              'sort_order' => 50],
                ['name' => 'All User Assigment',                     'slug' => 'all-user-assigment',                     'link' => 'assignment-method',                       'sort_order' => 60],
                ['name' => 'Role Based Volume Assignment',           'slug' => 'role-based-volume-assignment',           'link' => 'role-based-volume',                       'sort_order' => 74],
                ['name' => 'Transfer Application GroundWater',       'slug' => 'transfer-application-groundwater',       'link' => 'transfer-application',                    'sort_order' => 75],
                ['name' => 'Transfer Application Water Tanker',      'slug' => 'transfer-application-water-tanker',      'link' => 'application-transfer-wt',                 'sort_order' => 76],
                ['name' => 'Transfer Application Drilling Rig',      'slug' => 'transfer-application-drilling-rig',      'link' => 'trans-app-dr',                            'sort_order' => 77],
                ['name' => 'UnAssigned Applications',                'slug' => 'unassigned-applications',                'link' => 'assignment-method/unassigned-applications','sort_order' => 78],
                ['name' => 'Locked Applications',                    'slug' => 'locked-applications',                    'link' => 'locked_gw_app',                           'sort_order' => 89],
            ],
        ],

        // ── id=120 ───────────────────────────────────────────────────────────
        [
            'name'       => 'Log Management',
            'slug'       => 'log-management',
            'link'       => '#',
            'sort_order' => 53,
            'children'   => [
                ['name' => 'Update Approved Requests',    'slug' => 'update-approved-requests',    'link' => 'update-approved-requests',      'sort_order' => 41],
                ['name' => 'Activity Logs',               'slug' => 'activity-logs',               'link' => 'logs',                          'sort_order' => 42],
                ['name' => 'Sandes Log',                  'slug' => 'sandes-log',                  'link' => 'sandes-log',                    'sort_order' => 52],
                ['name' => 'Email Logs',                  'slug' => 'email-logs',                  'link' => 'email-log',                     'sort_order' => 54],
                ['name' => 'Payment Logs',                'slug' => 'payment-logs',                'link' => 'payment-log',                   'sort_order' => 55],
                ['name' => 'Monthly Payment Logs',        'slug' => 'monthly-payment-logs',        'link' => 'monthly-payment-log',           'sort_order' => 55],
                ['name' => 'GW Workflow Logs',            'slug' => 'gw-workflow-logs',            'link' => 'gw-workflow',                   'sort_order' => 56],
                ['name' => 'Revocation Logs',             'slug' => 'revocation-logs',             'link' => 'revocation-log',                'sort_order' => 56],
                ['name' => 'DR Workflow Logs',            'slug' => 'dr-workflow-logs',            'link' => 'dr-workflow',                   'sort_order' => 57],
                ['name' => 'WT Workflow Logs',            'slug' => 'wt-workflow-logs',            'link' => 'wt-workflow',                   'sort_order' => 58],
                ['name' => 'Credit History Logs',         'slug' => 'credit-history-logs',         'link' => 'credit-history',                'sort_order' => 59],
                ['name' => 'PPCB Logs',                   'slug' => 'ppcb-logs',                   'link' => 'ppcb-logs',                     'sort_order' => 70],
                ['name' => 'PSPCL Logs',                  'slug' => 'pspcl-logs',                  'link' => 'pspcl-logs',                    'sort_order' => 71],
                ['name' => 'Business First Portal Logs',  'slug' => 'business-first-portal-logs',  'link' => 'bfp-logs',                      'sort_order' => 72],
                ['name' => 'Accountant Verification Log', 'slug' => 'accountant-verification-log', 'link' => 'acc-verification-log',          'sort_order' => 79],
                ['name' => 'Interrim Permission Records', 'slug' => 'interrim-permission-records',  'link' => 'interim-permission',            'sort_order' => 80],
                ['name' => 'Dr Credit History',           'slug' => 'dr-credit-history',           'link' => 'dr-credit-history',             'sort_order' => 81],
                ['name' => 'Wt Credit History',           'slug' => 'wt-credit-history',           'link' => 'wt-credit-history',             'sort_order' => 82],
                ['name' => 'SMS Logs',                    'slug' => 'sms-logs',                    'link' => 'sms-log',                       'sort_order' => 83],
                ['name' => 'Round Robin Counter',         'slug' => 'round-robin-counter',         'link' => 'rr-counter',                    'sort_order' => 84],
                ['name' => 'Invoice Worklflow Log',       'slug' => 'invoice-worklflow-log',       'link' => 'inv-workflow',                  'sort_order' => 88],
                ['name' => 'Payments Unit Rebate',        'slug' => 'payments-unit-rebate',        'link' => 'payments_unit_allowed_rebates', 'sort_order' => 88],
                ['name' => 'Email In Queue',              'slug' => 'email-in-queue',              'link' => 'email-template/email-queue',    'sort_order' => 88],
                ['name' => 'Exception Log',               'slug' => 'exception-log',               'link' => 'exception-log',                 'sort_order' => 88],
                ['name' => 'Updated Reading Logs By Acc', 'slug' => 'updated-reading-logs-by-acc', 'link' => 'updated_reading',              'sort_order' => 88],
                ['name' => 'SMS/Sandes Queue',            'slug' => 'sms-sandes-queue',            'link' => 'ss-queue',                      'sort_order' => 89],
                ['name' => 'Unit Monthly Extraction',     'slug' => 'unit-monthly-extraction',     'link' => 'unit_monthly_ext',              'sort_order' => 90],
            ],
        ],

        // ── id=131 ───────────────────────────────────────────────────────────
        [
            'name'       => 'Mobile Menus',
            'slug'       => 'mobile-menus',
            'link'       => '#',
            'sort_order' => 6,
            'children'   => [
                ['name' => 'Question/Answers', 'slug' => 'question-answers', 'link' => 'ques-ans', 'sort_order' => 73],
            ],
        ],

        // ── id=143 ───────────────────────────────────────────────────────────
        [
            'name'       => 'Cancel Or Suspension',
            'slug'       => 'cancel-or-suspension',
            'link'       => '#',
            'sort_order' => 7,
            'children'   => [
                ['name' => 'Groundwater Application',  'slug' => 'groundwater-application',  'link' => 'cancel-suspension-gw', 'sort_order' => 83],
                ['name' => 'Drilling Rig Application', 'slug' => 'drilling-rig-application', 'link' => 'cancel-suspension-dr', 'sort_order' => 84],
                ['name' => 'Water Tanker Application', 'slug' => 'water-tanker-application', 'link' => 'cancel-suspension-wt', 'sort_order' => 85],
            ],
        ],

    ],
];
