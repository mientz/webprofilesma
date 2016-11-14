<?php
$app->group('/admin', function () {
    /**
     * [[dashboard Page]]
     */
    $this->get('', function ($req, $res, $args) {
        /*return $res->withJson($req->getAttributes());*/
        return $this->view->render($res, 'admin/dashboard.html', $req->getAttributes());
    })->setName('admin-dashboard');
})->add($session);
