<?php

class Users extends WebApp{
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
            $userData['groups'] = $this->getUserGroupsDetail($userData['id']);
            return $userData;
        }else{
            $userDatas = $getUsers->execute()->fetchAll();
            $userData = [];
            foreach($userDatas as $user){
                $user['status'] = json_decode($user['status']);
                $user['groups'] = $this->getUserGroupsDetail($user['id']);
                array_push($userData, $user);
            }
            return $userData;
        }
    }

    public function getUserGroupsDetail($id_user){
        $getUserGroups = $this->pdo
            ->select([
                'groups.id',
                'groups.`name`',
                'groups.name_url',
                'groups.header_image',
                'groups.meta',
                'user_group.role',
                'user_group.id as user_id',
                'groups.deleted'
            ])->from('groups')
            ->join('user_group', 'groups.id', '=', 'user_group.group_id')->where('groups.deleted', '=', 0)
            ->where('user_group.user_id', '=', $id_user)->execute()->fetchAll();
        return $getUserGroups;
    }

    public function setNewUsers($data, $id = null){
        if($id != null){
            return $this->pdo->update($data)->table('users')->where('id', '=', $id)->execute();
        }else{
            return $this->pdo->insert(array_keys($data))->into('users')->values(array_values($data))->execute(true);
        }
    }

    public function removeUsers($id){
        if($this->pdo->delete()->from('user_group')->where('user_id', '=', $id)->execute()){
            return $this->pdo->delete()->from('users')->where('id', '=', $id)->execute();
        }else{
            $this->pdo->delete()->from('users')->where('id', '=', $id)->execute();
        }
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
        return $this->view->render($res, 'admin/users/index.html', $req->getAttributes());
//        return $res->withJson($req->getAttributes());
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
    /**
     * Get User List
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  object                                   $args     URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function actionsRemoveUsers($req, $res, $args) {
        if($this->removeUsers($args['id'])){
            $res = $res->withJson(['success'=>true, 'messages'=>'Pengguna Telah Terhapus']);
        }else{
            $res = $res->withJson(['success'=>false, 'messages'=>'Pengguna gagal Terhapus']);
        }
        return $res;
    }
    /**
     * Get User List
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  object                                   $args     URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function actionsInviteUser($req, $res, $args) {
        $uri = $req->getUri()->getHost();
        $groupsInterface = new Groups($this->ci);
        if($this->pdo->select(['count(id)'])->from('users')->where('email', '=', $_POST['email'])->execute()->fetchColumn() < 1){
            $username = $this->generateRandomString();
            $token = base64_encode(json_encode([
                'username'      => $username,
                'email'         => $_POST['email'],
                'valid'         => date("Y-m-d H:i:s", strtotime("+1 week"))
            ]));
            $data = [
                'username'      => $username,
                'password'      => sha1($this->generateRandomString()),
                'email'         => $_POST['email'],
                'nickname'      => 'pengguna baru',
                'image'         => 'no-photo.png',
                'registered'    => date("Y-m-d H:i:s"),
                'status'        => json_encode([
                    'status'        => 'pending',
                    'token'         => $token,
                    'login'         => date("Y-m-d H:i:s")
                ])
            ];
            if($this->mailer->invite($_POST['email'], $uri.$this->router->pathFor('getAdminNewRegHTML', ['token' => $token]), 'lol')){
                $savedUser = $this->setNewUsers($data);
                if($savedUser){
                    if($_POST['role'] != null || $groupsInterface->setUserGroups(['user_id' => $savedUser, 'group_id'=>1, 'role' => $_POST['role']])){
                        $res = $res->withJson(['success'=>true, 'messages'=>'Invite Users']);
                    }else{
                        $res = $res->withJson(['success'=>true, 'messages'=>'Invite Users']);
                    }
                }else{
                    $res = $res->withJson(['success'=>false, 'messages'=>'Pengguna baru gagal tersimpan']);
                }
            }else{
                $res = $res->withJson(['success'=>false, 'messages'=>'Email undangan gagal terkirim']);
            }
        }else{
            $res = $res->withJson(['success'=>false, 'messages'=>'Telah terdapat pengguna dengan email yang sama']);
        }
        return $res;
    }

}
