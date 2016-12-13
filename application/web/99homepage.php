<?php
$app->group('/', function () {
    $this->get('', function ($req, $res, $args) {
    return $this->view->render($res, 'web/homepage.html');
    })->setName('homepage');

    $this->get('{year:[0-9]+}/{month:[0-9]+}/{day:[0-9]+}/{post:.+}.{ext:html}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/post-details.html');
    })->setName('post-detail');

    $this->get('{year:[0-9]+}/{month:[0-9]+}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/posts.html');
    })->setName('postby-date');

    $this->get('{page:.+}.{ext:html}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/page.html');
    })->setName('page');

    $this->get('{category}{ext:/}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/posts.html');
    })->setName('postby-category');

    $this->get('page/{id}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/homepage.html');
    })->setName('custom-page');

});
