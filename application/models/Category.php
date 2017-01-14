<?php
namespace Wijaya\WebApp\Models;

class Category extends \Wijaya\WebApp {
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

    public function getCategory(){
        return $this->pdo->select()->from('post_category')->execute()->fetchAll();
    }

    public function getCategoryById($category_id){
        return $this->pdo->select()->from('post_category')->where('id', '=', $category_id)->execute()->fetch();
    }

    public function getCategoryByGroupId($group_id){
        return $this->pdo->select()->from('post_category')->where('group_id', '=', $group_id)->execute()->fetchAll();
    }
}
