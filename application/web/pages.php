<?php
$app->get('/page', function ($req, $res, $args) {
    return $this->view->render($res, 'web/page.html');
})->setName('page');
