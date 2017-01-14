<?php
namespace Wijaya\WebApp\Admin;
class Post extends \Wijaya\WebApp{
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

    public function getPost($post_id = null, $type = 'all'){
        if($post_id != null){
            return $this->pdo->select()->from('get_post_detail_all')->where('id', '=', $post_id)->execute()->fetch();
        }else{
            return $this->pdo->select()->from('get_post_detail_'.$type)->where('group_id', '=', $this->group_id)->groupBy('id')->orderBy('date', 'DESC')->execute()->fetchAll();
        }
    }

    public function View($req, $res, $args = ['type'=>'all']){
       $req = $req->withAttribute('sidemenu', ['post'=>'all']);
       $req = $req->withAttribute('post_tab', (isset($_GET['type']) ? $_GET['type'] : 'all'));
       $req = $req->withAttribute('posts', $this->getPost(null, (isset($_GET['type']) ? $_GET['type'] : 'all')));
       return $this->view->render($res, 'admin/post/index.html', $req->getAttributes());
    }

    public function __invoke($req, $res, $args){
        return $res->withJson($this->getPost());
    }
}
