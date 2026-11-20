<?php

return [
    // Zona waktu & titik tengah peta default (Jakarta)
    'timezone' => env('SIGAP_TIMEZONE', 'Asia/Jakarta'),
    'map_center' => [-6.2088, 106.8456],

    // Batas laporan per warga per hari (anti-spam)
    'daily_report_limit' => (int) env('SIGAP_DAILY_REPORT_LIMIT', 5),

    // Kompresi foto (Intervention Image)
    'image' => [
        'max_width' => 1280,
        'quality' => 75,
        'thumb_width' => 480,
        'thumb_height' => 320,
        'thumb_quality' => 70,
    ],
];
