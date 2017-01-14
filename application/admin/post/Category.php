<?php
namespace Wijaya\WebApp\Admin;
class Category extends \Wijaya\WebApp{
    /*
     * This Class Only Variable
     */
    protected $ci;
    protected $user_id;
    protected $group_id;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->user_id = $this->session->user_id;
        $this->group_id = $this->getUserGroupData($this->user_id, $this->session->groupKey)['id'];
    }

    public function getCategory($category_id = null){
        if($category_id != null){
            return $this->pdo->select()->from('post_category')->where('id', '=', $category_id)->execute()->fetch();
        }else{
            $this->pdo->select()->from('post_category')->where('deleted', '=', 0)->execute()->fetchAll();
        }
    }
}
