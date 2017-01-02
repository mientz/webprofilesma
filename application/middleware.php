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
        $this->flash->addMessage('lastpage_attr', $route->getArguments());
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
    $req = $req->withAttribute('user_group', $select->fetchAll(PDO::FETCH_ASSOC));
    $group_id = ($this->session->group_id == null ? $req->getAttribute('user_group')[0]['group_id'] : $this->session->group_id);
    $select = $this->db->prepare("
        select *
        from
            groups
        where
            id=:group_id
    ");
    $select->bindParam(':group_id', $group_id, PDO::PARAM_INT);
    $select->execute();
    $req = $req->withAttribute('current_group_data', $select->fetch(PDO::FETCH_ASSOC));
    $req = $req->withAttribute('current_group_url', ($group_id == 1 ? null : $req->getAttribute('current_group_data')['name']));



    /*
     * list categorry by current group
     */
    $this->flash->addMessage('lastpage_group_change', $route->getName());
    $res = $next($req, $res);
    return $res;
};


$app->add(function ($req, $res, $next) {
    $uri = $req->getUri()->getBaseUrl();
    $req = $req->withAttribute('uri_base', $uri);
    $res = $next($req, $res);
    return $res;
});

/*
 * Web front global
 */
$global = function ($req, $res, $next) {
    // main menu
    $menu = $this->db->query("select meta from menus where group_id='1'")->fetchColumn();
    $req = $req->withAttribute('main_menu', json_decode($menu));

    // web settings
    $settings = [];
    foreach($this->db->query("select * from settings")->fetchAll(PDO::FETCH_ASSOC) as $val){
        $settings[$val['type']] = [];
        foreach(json_decode($val['config']) as $key => $config){
            $settings[$val['type']][$key] = base64_decode($config);
        }
    }
    $req = $req->withAttribute('settings', $settings);
    $res = $next($req, $res);
    return $res;
};

