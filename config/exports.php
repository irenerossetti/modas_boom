<?php

return [
    // If true, export routes for debugging (PDF/CSV) will be registered without auth;
    // this is only intended for local/testing environments and should be set to false in production.
    'noauth_enabled' => env('EXPORT_NOAUTH_ENABLED', false),
    // Additional safety: only allow noauth exports when app()->environment('local') is true.
    // CSV delimiter to use by default for CSV exports. Many Spanish Excel installs expect ';'.
    'csv_delimiter' => env('CSV_DELIMITER', ';'),
];
