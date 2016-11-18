<?php
$app->group('/admin/post', function () {
    /**
     * [[post page]]
     */
    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost');

    /**
     * add new post page
     */
    $this->get('/add', function ($req, $res, $args) {
        $req = $req->withAttribute('addpost', 'active');

        return $this->view->render($res, 'admin/add-post.html', $req->getAttributes());
    })->setName('admin-addpost');
})->add($session);
