<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'xero' => [
        'client_id'           => env('XERO_CLIENT_ID'),
        'client_secret'       => env('XERO_CLIENT_SECRET'),
        'redirect_uri'        => env('XERO_REDIRECT_URI'),
        'payroll_calendar_id' => env('XERO_PAYROLL_CALENDAR_ID'),
        // Level 1
        'rate_weekday_l1' => env('XERO_RATE_WEEKDAY_L1'),
        'rate_night_l1'   => env('XERO_RATE_NIGHT_L1'),
        'rate_sat_l1'     => env('XERO_RATE_SAT_L1'),
        'rate_sun_l1'     => env('XERO_RATE_SUN_L1'),
        'rate_ph_l1'      => env('XERO_RATE_PH_L1'),

        // Level 2
        'rate_weekday_l2' => env('XERO_RATE_WEEKDAY_L2'),
        'rate_night_l2'   => env('XERO_RATE_NIGHT_L2'),
        'rate_sat_l2'     => env('XERO_RATE_SAT_L2'),
        'rate_sun_l2'     => env('XERO_RATE_SUN_L2'),
        'rate_ph_l2'      => env('XERO_RATE_PH_L2'),

        // Level 3
        'rate_weekday_l3' => env('XERO_RATE_WEEKDAY_L3'),
        'rate_night_l3'   => env('XERO_RATE_NIGHT_L3'),
        'rate_sat_l3'     => env('XERO_RATE_SAT_L3'),
        'rate_sun_l3'     => env('XERO_RATE_SUN_L3'),
        'rate_ph_l3'      => env('XERO_RATE_PH_L3'),

        // Level 4
        'rate_weekday_l4' => env('XERO_RATE_WEEKDAY_L4'),
        'rate_night_l4'   => env('XERO_RATE_NIGHT_L4'),
        'rate_sat_l4'     => env('XERO_RATE_SAT_L4'),
        'rate_sun_l4'     => env('XERO_RATE_SUN_L4'),
        'rate_ph_l4'      => env('XERO_RATE_PH_L4'),

        // Level 5
        'rate_weekday_l5' => env('XERO_RATE_WEEKDAY_L5'),
        'rate_night_l5'   => env('XERO_RATE_NIGHT_L5'),
        'rate_sat_l5'     => env('XERO_RATE_SAT_L5'),
        'rate_sun_l5'     => env('XERO_RATE_SUN_L5'),
        'rate_ph_l5'      => env('XERO_RATE_PH_L5'),

        // DHHS L3
        'rate_weekday_dhhs_l3' => env('XERO_RATE_WEEKDAY_DHHS_L3'),
        'rate_night_dhhs_l3'   => env('XERO_RATE_NIGHT_DHHS_L3'),
        'rate_sat_dhhs_l3'     => env('XERO_RATE_SAT_DHHS_L3'),
        'rate_sun_dhhs_l3'     => env('XERO_RATE_SUN_DHHS_L3'),
        'rate_ph_dhhs_l3'      => env('XERO_RATE_PH_DHHS_L3'),

        // Airport L1
        'rate_weekday_airport_l1' => env('XERO_RATE_WEEKDAY_AIRPORT_L1'),
        'rate_night_airport_l1'   => env('XERO_RATE_NIGHT_AIRPORT_L1'),
        'rate_sat_airport_l1'     => env('XERO_RATE_SAT_AIRPORT_L1'),
        'rate_sun_airport_l1'     => env('XERO_RATE_SUN_AIRPORT_L1'),
        'rate_ph_airport_l1'      => env('XERO_RATE_PH_AIRPORT_L1'),

        // Travel (shared across all levels)
        'rate_travel' => env('XERO_RATE_TRAVEL'),
        'ordinary_earnings_rate_id' => env('XERO_ORDINARY_EARNINGS_RATE_ID'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'microsoft_graph' => [
        'tenant_id' => env('TENANT_ID'),
        'client_id' => env('CLIENT_ID'),
        'client_secret' => env('CLIENT_SECRET'),
        'redirect_uri' => env('REDIRECT_URI', 'https://app.thescouts.com.au/auth-callback'),
        'notification_endpoint' => env('NOTIFICATION_ENDPOINT'),
    ],
];
