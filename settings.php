<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        "database" => [
            "host" => "localhost",
            "database_name" => "sma3bangkalan",
            "user" => "root",
            "pass" => ""
        ],
        "server" => "192.168.100.8",
        "template"=>[
            "cache" => false,
            "cache_location" => "template/cache"
        ]
    ],
];
