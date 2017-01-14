<?php
namespace Wijaya\WebApp\Models;
class Users extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $groupsModel;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->groupsModel = new \Wijaya\WebApp\Models\Groups($ci);
    }

    public function getUsers(){
        $users = $this->pdo->select()->from('users')->where('deleted', '=', 0)->execute()->fetchAll();
        foreach ($users as &$value) {
            $value['status'] = json_decode($value['status'], true);
        }
        return $users;
        unset($value);
    }

    public function getUserById($id){
        $user = $this->pdo->select()->from('users')->where('id', '=', $id)->where('deleted', '=', 0)->execute()->fetch();
        $user['status'] = json_decode($user['status'], true);
        return $user;
    }

    public function getUsersGroups(){
        $users = $this->getUsers();
        foreach ($users as &$user) {
            $userGroups = $this->pdo->select()->from('user_group')->where('user_id', '=', $user['id'])->execute()->fetchAll();
            foreach ($userGroups as &$userGroup) {
                $userGroup['detail'] = $this->groupsModel->getGroupById($userGroup['group_id']);
                unset($userGroup['group_id']);
            }
            $user['groups'] = $userGroups;
            unset($userGroup);
        }
        return $users;
        unset($user);
    }

    public function getUserGroupsById($id){
        $user = $this->getUserById($id);
        $userGroups = $this->pdo->select()->from('user_group')->where('user_id', '=', $user['id'])->execute()->fetchAll();
        foreach ($userGroups as &$userGroup) {
            $userGroup['detail'] = $this->groupsModel->getGroupById($userGroup['group_id']);
            unset($userGroup['group_id']);
        }
        $user['groups'] = $userGroups;
        unset($userGroup);
        return $user;
    }

    public function getUserByUsernameAndPassword($username, $password){
        $user = $this->pdo->prepare('select * from users where password=:password and ( username=:username or email=:email )');
        $user->bindValue(':password', sha1($password));
        $user->bindValue(':username', $username);
        $user->bindValue(':email', $username);
        $user->execute();
        return $user->fetch();
    }
}
