<?php
namespace Wijaya;
/**
 * Slim Framework (http://slimframework.com)
 *
 * @copyright Copyright (c) 2016-2017 Amin Wijaya
 */
class WebApp{
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
    protected $report;
    /**
     * Main WEB App Constructor
     * @method __construct
     * @param  function      $container Slim Container Depedency Injector
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
        $this->report = $container->get('report');
    }
    /**
     * Random String Generator
     * @param  integer $length  String Length (default 10)
     * @return String
     */
    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    /**
     * Get User Details from given users.id
     * @param  [integer] $user_id [users.id]
     * @return [object]          [users data Detail]
     */
    public function getUserDetail($user_id){
        $user_data = $this->pdo->select()->from('users')->where('id', '=', $user_id)->execute()->fetch();
        $user_data["status"] = json_decode($user_data["status"], true);
        $user_data["privilege"] = $this->getUserGroupData($user_id, $this->session->groupKey);
        return $user_data;
    }
    /**
     * Get Users Groups Data from given Users.id
     * @method getUserGroupsData
     * @param  integer            $user_id Users.id
     * @return object                     Users Groups Data
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
     * Get User Groups Data from given Users.id and Groups Array key From session GroupPosBased
     * @method getUserGroupData
     * @param  integer           $user_id   Users.id
     * @param  integer           $groupsKey groups sessions array key
     * @return object                      selected groups data from array key
     */
    public function getUserGroupData($user_id, $groupsKey){
        if($this->getUserGroupsData($user_id)){
            return $this->getUserGroupsData($user_id)[$groupsKey];
        }else{
            return false;
        }

    }

}
