<?php
namespace Wijaya\WebApp\Models;

class Tags extends \Wijaya\WebApp {
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

    public function getTags(){
        return $this->pdo->select()->from('tags')->execute()->fetchAll();
    }

    public function getTagsById($tag_id){
        return $this->pdo->select()->from('tags')->where('id', '=', $tag_id)->execute()->fetchAll();
    }

    public function getTagsIdByName($tag_name){
        return $this->pdo->select(['id'])->from('tags')->where('name', '=', $tag_name)->execute()->fetchColumn();
    }
}
