<?php
$app->group('/do', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/invitation[/{token}]', function ($req, $res, $args) {
        return $this->view->render($res, 'admin/users.html', $req->getAttributes());
    })->setName('getDoInvitationHTML');
});
