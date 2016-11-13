<?php
$app->group('/admin', function () {
    /**
     * [[dashboard Page]]
     */
    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('dashboard', 'active');

        return $this->view->render($res, 'admin/dashboard.html', $req->getAttributes());
    })->setName('admin-dashboard');
})->add($user_session_data);
