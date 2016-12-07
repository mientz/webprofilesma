<?php
/*
 * session settings
 */
$app->add(new \Slim\Middleware\Session([
    'name' => 'SMA_N_3_BANGKALAN',
    'autorefresh' => true,
    'lifetime' => '1 days'
]));

/*
 * data by session
 */
$session = function ($req, $res, $next) {
    /*
     * session check
     */
    $route = $req->getAttribute('route');
    if(!isset($this->session->user_id)){
        $this->flash->addMessage('lastpage', $route->getName());
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login'));
    }

    /*
     * User logged data
     */
    $user_id = $this->session->user_id;
    $select = $this->db->prepare("select * from users where id=:user_id group by id");
    $select->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $select->execute();
    $user_data = $select->fetch(PDO::FETCH_ASSOC);
    $user_data["status"] = json_decode($user_data["status"], true);
    $user_data["privilege"] = json_decode($user_data["privilege"], true);
    $req = $req->withAttribute('user_data', $user_data);

    /*
     * current group data
     */
    $select = $this->db->prepare("
        select *
        from
            groups, user_group
        where
            groups.id=user_group.group_id
            and user_group.user_id=:user_id
        group by groups.id
    ");
    $select->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $select->execute();
    $req = $req->withAttribute('current_group_data', $select->fetch(PDO::FETCH_ASSOC));



    /*
     * list categorry by current group
     */

    $res = $next($req, $res);
    return $res;
};


$app->add(function ($req, $res, $next) {
    $uri = $req->getUri()->getBaseUrl();
    $req = $req->withAttribute('uri_base', $uri);
    $res = $next($req, $res);
    return $res;
});

