<?php
return [
    'settings' => [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => true,
        "database" => [
            "host" => "localhost",
            "database_name" => "website_sma",
            "user" => "root",
            "pass" => ""
        ],
        "server" => "192.168.100.8",
        "template"=>[
            "cache" => false,
            "cache_location" => "template/cache"
        ],
        'debug' => true,
        'whoops.editor' => 'sublime'
    ],
];
