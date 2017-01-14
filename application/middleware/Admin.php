<?php
class AdminMiddleware extends WebApp{
    /**
     * Slim Framework Container RunInterface
     * @var Slim\Container
     */
    protected $ci;
    /**
     * Admin Class Constructor
     * @method __construct
     * @param  Slim\Container      $ci Slim\Container
     */
    public function __construct($ci){
       $this->ci = $ci;
       parent::__construct($ci);
    }
    /**
     * Middleware Admin session
     * @method __invoke
     * @param  Psr\Http\Message\ServerRequestInterface      $req  PSR request
     * @param  Psr\Http\Message\ResponseInterface           $res  PSR response
     * @param  callable                                     $next Next Middleware
     * @return Psr\http\Psr\Http\Message\ResponseInterface
     */
    public function __invoke($req, $res, $next){
        $group_id;
        $route = $req->getAttribute('route');
        if(!isset($this->session->user_id)){

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminAuthLoginHTML'));

        }
        $user_id = $this->session->user_id;
        if($this->getUserGroupData($user_id, $this->session->groupKey)){
            $group_id = $this->getUserGroupData($user_id, $this->session->groupKey)['id'];
        }else{
            $group_id = false;
        }
        $req = $req->withAttribute('user_data', $this->getUserDetail($user_id));
        $req = $req->withAttribute('currentUserGroups', $this->getUserGroupsData($user_id));
        $req = $req->withAttribute('currentUserGroup', $this->getUserGroupData($user_id, $this->session->groupKey));
        $req = $req->withAttribute('currentUserGroupID', $group_id);
        $req = $req->withAttribute('currentUserGroupUrl', ($group_id == 1 ? null : $this->getUserGroupData($user_id, $this->session->groupKey)['name']));
        $res = $next($req, $res);
        return $res;
    }

}
