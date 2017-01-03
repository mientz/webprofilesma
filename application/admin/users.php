<?php

class Users extends Admin{
    /*
     * This Class Only Variable
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci) {
        $this->ci = $ci;
        parent::__construct($ci);
    }

    public function getUserDetail($id = null){
        $getUsers = $this->pdo->select()->from('users')->where('deleted', '=', 0);
        if($id != null){
            $userData = $getUsers->where('id', '=', $id)->execute()->fetch();
            $userData['status'] = json_decode($userData['status']);
            $userData['privilege'] = json_decode($userData['privilege']);
            $userData['groups'] = $this->getUserGroupsDetail($userData['id']);
            return $userData;
        }else{
            $userDatas = $getUsers->execute()->fetchAll();
            $userData = [];
            foreach($userDatas as $user){
                $user['status'] = json_decode($user['status']);
                $user['privilege'] = json_decode($user['privilege']);
                $user['groups'] = $this->getUserGroupsDetail($user['id']);
                array_push($userData, $user);
            }
            return $userData;
        }
    }

    public function getUserGroupsDetail($id_user){
        $getUserGroup = $this->pdo->select()->from('user_group')->where('user_id', '=', $id_user)->execute()->fetchAll();
        $groupsDetail = [];
        foreach($getUserGroup as $userGroup){
            $groups = $this->pdo->select()->from('groups')->where('id', '=', $userGroup['group_id'])->execute()->fetch();
            array_push($groupsDetail, $groups);
        }
        return $groupsDetail;
    }

    public function setNewUsers($data){
        return $this->pdo->insert(array_keys($data))->into('users')->values(array_values($data))->execute(true);
    }

    public function sendInvitationEmail($data){

    }
    /**
     * Get User List
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  object                                   $args     URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function getUserLists($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['users'=>'list']);
        $req = $req->withAttribute('users', $this->getUserDetail());
        //        $this->mailer->invite('genthowijaya@gmail.com', $this->view->render($res, 'email/invite.html', $req->getAttributes()));
        return $this->view->render($res, 'admin/users/index.html', $req->getAttributes());
    }
    /**
     * Get User List
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  object                                   $args     URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function actionsGetUserLists($req, $res, $args) {
        $res = $res->withJson($this->getUserDetail());
        return $res;
    }

}
