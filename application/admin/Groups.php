<?php

class Groups extends WebApp{
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

    public function getGroupsDetails($id = null, $excludeMain = true){
        $groupsData = $this->pdo->select()->from('groups')->where('deleted', '=', 0);
        if($excludeMain){
            $groupsData->where('id', '<>', 1);
        }
        if($id != null){
            $users = new Users($this->ci);
            $groupsResult = $groupsData->where('id', '=', $id)->execute()->fetch();
            $groupsResult['userGroups'] = $this->getUsersGroups($id);
            $groupsResult['userConected'] = [];
            foreach($groupsResult['userGroups'] as $key => $user){
                $currentGroup = $groupsResult['userGroups'][$key];
                $currentGroup['user_detail'] = $users->getUserDetail($user['user_id']);
                $groupsResult['userGroups'][$key] = $currentGroup;
            }
            $groupsResult['meta'] = json_decode($groupsResult['meta'], true);
            return $groupsResult;
        }else{
            $groupsResult = $groupsData->execute()->fetchAll();
            foreach($groupsResult as $key => $group){
                $groupsResult[$key]['userGroups'] = $this->getUsersGroups($groupsResult[$key]['id']);
                $groupsResult[$key]['admin'] = $this->getGroupsAdmin($groupsResult[$key]['id']);
                $groupsResult[$key]['meta'] = json_decode($groupsResult[$key]['meta'], true);
            }
            return $groupsResult;
        }
    }
    public function getGroupsAdmin($group_id){
        $admin = [];
        foreach($this->getUsersGroups($group_id) as $users){
            if($users['role'] == 'administrator' || $users['role'] == 'editor'){
                $user = $this->pdo->select()->from('users')->where('id', '=', $users['user_id'])->execute()->fetch();
                array_push($admin, $user);
            }
        }
        return $admin;
    }
    public function getUsersGroups($groups_id){
        return $this->pdo->select()->from('user_group')->where('group_id', '=', $groups_id)->groupBy('user_id')->execute()->fetchAll();
    }
    public function setUserGroups($data, $id = null){
        if($id != null){
            return $this->pdo->update($data)->table('user_group')->where('id', '=', $id)->execute();
        }else{
            return $this->pdo->insert(array_keys($data))->into('user_group')->values(array_values($data))->execute(true);
        }
    }
    public function setGroups($data, $id = null){
        if($id != null){
            return $this->pdo->update($data)->table('groups')->where('id', '=', $id)->execute();
        }else{
            return $this->pdo->insert(array_keys($data))->into('groups')->values(array_values($data))->execute(true);
        }
    }
    public function removeUserGroups($id){
        return $this->pdo->delete()->from('user_group')->where('id', '=', $id)->execute();
    }
    public function displayGroupList($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['groups'=>'list']);
        $req = $req->withAttribute('groups', $this->getGroupsDetails());
        return $this->view->render($res, 'admin/groups/index.html', $req->getAttributes());
    }
    public function displayEditGroup($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['groups'=>'list']);
        $req = $req->withAttribute('groups', $this->getGroupsDetails($args['id']));
        return $this->view->render($res, 'admin/groups/edit.html', $req->getAttributes());
//        return $res->withJson($req->getAttribute('groups'));
    }
    public function actionsNewGroups($req, $res, $args){
        $type = pathinfo('public/groups/no-logo.png', PATHINFO_EXTENSION);
        $data = file_get_contents('public/groups/no-logo.png');
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        $data = [
            "name"          => $_POST['name'],
            "name_url"      => $this->slug->make($_POST['name']),
            "header_image"  => '13385771_255950824767690_1897064539_n.jpg',
            "meta"          => json_encode([
                "created"   => date("Y-m-d H:i:s"),
                "logo"      => $base64
            ])
        ];
        $setGroups = $this->setGroups($data);
        if($setGroups){
            $setUserGroup = $this->setUserGroups([
                'group_id'  => $setGroups,
                'user_id'   => $_POST['admin'],
                'role'      => 'editor'
            ]);
            if($setUserGroup){
                $res = $res->withJson(['success'=>true, 'actions'=>'Create New Groups']);
            }else{
                $res = $res->withJson(['success'=>false, 'actions'=>'Create New Groups']);
            }
        }
        return $res;
    }

    public function actionsDeleteGroups($req, $res, $args){
        if($this->setGroups(['deleted'=>1], $args['id'])){
            $res = $res->withJson(['success'=>true, 'actions'=>'Delete Groups '.$_POST['name']]);
        }
        return $res;
    }

    public function actionsSimpleEditGroup($req, $res, $args){
        $oldMeta = json_decode($_POST['meta'], true);
        $data = [
            'name'      => $_POST['name'],
            'name_url'  => $this->slug->make($_POST['name']),
            'meta'      => json_encode([
                'created'   => $oldMeta['created'],
                'logo'      => $_POST['logo']
            ])
        ];
        if($this->setGroups($data, $args['id'])){
            $res = $res->withJson(['success'=>true, 'actions'=>'Edit Groups '.$_POST['name']]);
        }
        return $res;
    }

    public function actionsChangeUserGroupRole($req, $res, $args){
        if($this->setUserGroups(['role'=>$_POST['role']], $args['id'])){
            $res = $res->withJson(['success'=>true, 'actions'=>'Change User Group Role']);
        }
        return $res;
    }

    public function actionsKickUserGroupRole($req, $res, $args){
        if($this->removeUserGroups($args['id'])){
            $res = $res->withJson(['success'=>true, 'actions'=>'kick user from groups']);
        }
        return $res;
    }

    public function actionsAddUserGroupRole($req, $res, $args){
        $data = [
            'group_id'  => $_POST['group_id'],
            'user_id'   => $_POST['user'],
            'role'      => $_POST['role'],
        ];
        if($this->setUserGroups($data)){
            $res = $res->withJson(['success'=>true, 'actions'=>'add user to groups']);
        }
        return $res;
    }

}
