<?php
namespace Wijaya\WebApp\Models;
class Groups extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
    }

    public function getGroups(){
        $groups = $this->pdo->select()->from('groups')->where('deleted', '=', 0)->execute()->fetchAll();
        foreach ($groups as &$group) {
            $group['meta'] = json_decode($group['meta'], true);
        }
        return $groups;
        unset($group);
    }

    public function getGroupById($id){
        $group = $this->pdo->select()->from('groups')->where('id', '=', $id)->where('deleted', '=', 0)->execute()->fetch();
        $group['meta'] = json_decode($group['meta'], true);
        return $group;
    }
}
