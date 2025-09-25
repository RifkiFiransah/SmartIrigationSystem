<?php

return [
    /*
    |--------------------------------------------------------------------------
    | System Report Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for system reporting functionality
    |
    */

    // Default date range for reports (days)
    'default_days_back' => env('REPORT_DEFAULT_DAYS_BACK', 7),

    // Cache TTL for report data (minutes)
    'cache_ttl_minutes' => env('REPORT_CACHE_TTL', 5),

    // Maximum records to export for sensor raw data
    'sensor_data_export_limit' => env('REPORT_SENSOR_EXPORT_LIMIT', 50000),

    // Email schedule time (24h format)
    'daily_email_time' => env('REPORT_EMAIL_TIME', '07:00'),

    // Whether to include charts in email reports
    'email_include_charts' => env('REPORT_EMAIL_CHARTS', false),

    // Report file formats enabled
    'export_formats' => [
        'csv' => true,
        'excel' => true,
        'pdf' => env('REPORT_PDF_ENABLED', false), // Enable when PDF dependency is installed
    ],
];