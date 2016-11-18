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



