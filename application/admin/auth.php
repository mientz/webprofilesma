<?php
$app->group('/auth', function () {
    /**
     * [[Login Page]]
     */
    $this->get('/login[/{error}]', function ($req, $res, $args) {
        return $this->view->render($res, 'admin/login.html', [
            'error' => (array_key_exists('error', $args) ? true : false),
            'flash' => $this->flash->getMessages()
        ]);
    })->setName('pre-login');

    /*
     * [[Login Process]]
     */
    $this->post('/login', function ($req, $res, $args) {
        $select = $this->db->prepare("select * from sma_users where user_password=:password and (user_username=:username or user_email=:email) and user_deleted = '0' group by user_id");
        $select->bindParam(':username', $_POST["username"], PDO::PARAM_STR);
        $select->bindParam(':email', $_POST["username"], PDO::PARAM_STR);
        $select->bindParam(':password', sha1($_POST["password"]), PDO::PARAM_STR);
        $select->execute();
        $count = $select->rowCount();
        $data = $select->fetch(PDO::FETCH_ASSOC);
        if($count == 1){
            $this->session->set('user_id', $data["user_id"]);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor((isset($_POST["lastpage"]) ? $_POST["lastpage"] : 'admin-dashboard')));
        }else{
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login')."/error");
        }
        return $res;
    })->setName('login');
})->add(function ($req, $res, $next) {
    if(isset($this->session->user_id)){
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-dashboard'));
    }
    $res = $next($req, $res);
    return $res;
    return $res;
});


/*
 * [[logout]]
 */
$app->get('/logout', function ($req, $res, $args) {
    $this->session->destroy();
    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login'));
})->setName('logout');
