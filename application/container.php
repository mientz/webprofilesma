<?php

/**
 * [[Database connection container]]
 * @param callback $container [[container parameter]]
 */
$container['db'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['database_name'], $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

/**
 * [[Twig templating container]]
 * @param callback $container [[container parameter]]
 */
$container['view'] = function ($container) {
    $settings = $container->get('settings')['template'];
    if ($settings['cache']) {
        $view = new \Slim\Views\Twig('template', [
            'cache' => $settings['cache_location']
        ]);
    }else{
        $view = new \Slim\Views\Twig('template');
    }
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));

    return $view;
};

/*
 * session
 */
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

/*
 * Flash message register
 */
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

/*
 * Flash message register
 */
$container['manager'] = function () {
    return new \Intervention\Image\ImageManager(array('driver' => 'gd'));
};

/*
 * Sluger
 */
$container['slug'] = function ($string){
    return new Slug($string);
};

/*
 * files size converter
 */
$container['filesize'] = function ($string){
    return new FileSize($string);
};

class Slug {
    public $string;
    function __construct($string) {
        $this->string = $string;
    }
    public function make($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
    public function file($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z\.]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
}

class FileSize {
    public $bytes;
    function __construct($bytes) {
        $this->bytes = $bytes;
    }
    public function convert($bytes){
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );
        foreach($arBytes as $arItem){
            if($bytes >= $arItem["VALUE"]){
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}



