<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$settings = require 'settings.php';
$app = new \Slim\App($settings);

$container = $app->getContainer();

//auto load all php file

$require_dir = [
    "application",
    "application/admin",
    "application/web",
];
foreach ($require_dir as $dir){
    foreach (glob($dir."/*.php") as $filename){
        require $filename;
    }
}
$app->run();
