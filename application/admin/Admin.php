<?php
class Admin{
    protected $db;
    protected $slug;
    protected $view;
    protected $mailer;
    protected $session;
    protected $flash;
    protected $manager;
    protected $diff;
    protected $filesize;
    protected $router;

    /**
     * Default Admin Class Constructor
     * @private
     * @param function $container Slim Framework Container Interface
     */
    function __construct($container) {
        $this->db = $container->get('db');
        $this->pdo = $container->get('pdo');
        $this->slug = $container->get('slug');
        $this->session = $container->get('session');
        $this->diff = $container->get('diff');
        $this->filesize = $container->get('filesize');
        $this->tags = $container->get('tags');
        $this->mailer = $container->get('mailer');
        $this->view = $container->get('view');
        $this->router = $container->get('router');
        $this->flash = $container->get('flash');
    }
    /**
     * Get Users Detail By ID
     * @param  integer $user_id user id by sessions
     * @return object user details with json decoded
     */
    public function getUserDetail($user_id){

        $user_data = $this->pdo->select()->from('users')->where('id', '=', $user_id)->execute()->fetch();
        $user_data["status"] = json_decode($user_data["status"], true);
        $user_data["privilege"] = $this->getUserGroupData($user_id, $this->session->groupKey);

        return $user_data;

    }
    /**
     * Get All Groups Data from current user
     * @param  integer $user_id current user id
     * @return object groups data
     */
    public function getUserGroupsData($user_id){

        $user_groups = $this->pdo
            ->select([
                'groups.id', 'groups.`name`', 'groups.name_url', 'groups.header_image', 'groups.meta', 'groups.deleted', 'user_group.role'
            ])
            ->from('groups')
            ->join('user_group', 'groups.id', '=', 'user_group.group_id')
            ->where('user_group.user_id', '=', $user_id)
            ->where('groups.deleted', '=', 0)
            ->execute();
        if($user_groups){
            return $user_groups->fetchAll();
        }else{
            return false;
        }

    }
    /**
     * Get selected Group Data from current user
     * @param  integer $user_id current user id
     * @return object selected group data
     */
    public function getUserGroupData($user_id, $groupsKey){
        if($this->getUserGroupsData($user_id)){
            return $this->getUserGroupsData($user_id)[$groupsKey];
        }else{
            return false;
        }

    }
    /**
     * Admin Page Middleware(Sessions)
     * @private
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  callable                                 $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
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
