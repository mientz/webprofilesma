<?php
/*
 * session
 */
$app->add(new \Slim\Middleware\Session([
    'name' => 'stars',
    'autorefresh' => true,
    'lifetime' => '1 minutes'
]));

/*
 * session
 */
$user_session_data = function ($req, $res, $next) {
    $route = $req->getAttribute('route');
    if(!isset($this->session->user_id)){
        $this->flash->addMessage('lastpage', $route->getName());
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login'));
    }

    $user_id = $this->session->user_id;
    $select = $this->db->prepare("select * from sma_users where user_id=:user_id group by user_id");
    $select->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $select->execute();
    $req = $req->withAttribute('user_data', $select->fetch(PDO::FETCH_ASSOC));
    $res = $next($req, $res);
    return $res;
};

$app->add(function ($req, $res, $next) {
    $uri = $req->getUri()->getBaseUrl();
    $req = $req->withAttribute('uri_base', $uri);
    $res = $next($req, $res);
    return $res;
});
