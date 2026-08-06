<?php

return [
    'timezone' => env('ATTENDANCE_TIMEZONE', env('APP_TIMEZONE', 'Asia/Jakarta')),

    'token_rotation_seconds' => (int) env('ATTENDANCE_TOKEN_ROTATION_SECONDS', 10),

    'scan_tolerance_seconds' => (int) env('ATTENDANCE_SCAN_TOLERANCE_SECONDS', 0),

    'device' => [
        'enforce_single_browser_per_student' => filter_var(
            env('ATTENDANCE_ENFORCE_SINGLE_BROWSER', false),
            FILTER_VALIDATE_BOOLEAN
        ),
    ],
];
