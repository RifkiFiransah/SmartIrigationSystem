<?php
return [
    'provider' => env('WEATHER_PROVIDER', 'open-meteo'), // bmkg|open-meteo
    'bmkg' => [
        // Contoh endpoint prakiraan kecamatan (adm4) diberikan user
        'forecast_url' => env('BMKG_FORECAST_URL', 'https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=32.08.10.2001'),
        // Map kondisi BMKG -> internal condition (simple mapping)
        'condition_map' => [
            'Cerah' => 'cerah',
            'Cerah Berawan' => 'cerah',
            'Berawan' => 'mendung',
            'Berawan Tebal' => 'mendung',
            'Hujan Ringan' => 'hujan',
            'Hujan Sedang' => 'hujan',
            'Hujan Lebat' => 'hujan',
            'Hujan Petir' => 'hujan',
            'Kabut' => 'mendung',
        ],
    ],
];
