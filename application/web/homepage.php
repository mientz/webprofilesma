<?php
$app->get('/', function ($req, $res, $args) {
    return $this->view->render($res, 'web/homepage.html');
})->setName('homepage');
