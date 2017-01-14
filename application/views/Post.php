<?php
namespace Wijaya\WebApp\View;
class Post extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $postModel;
    protected $userModel;
    protected $tagsModel;
    protected $activeUserGroup;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->postModel = new \Wijaya\WebApp\Models\Post($ci);
        $this->userModel = new \Wijaya\WebApp\Models\Users($ci);
        $this->tagsModel = new \Wijaya\WebApp\Models\Tags($ci);
        $this->activeUserGroup = $this->userModel->getUsersGroups()[$this->session->groupKey]['groups'];
    }

    public function getPosts($req, $res, $args){
        $group_id = $this->activeUserGroup[0]['id'];
        $req = $req->withAttribute('PostByCategories', $this->postModel->getPostByGroupId($group_id));
        $req = $req->withAttribute('PostTags', $this->postModel->getPostTags());
        $req = $req->withAttribute('Tags', $this->tagsModel->getTags());
        $req = $req->withAttribute('Users', $this->userModel->getUsers());
        $req = $req->withAttribute('Tab', (isset($_GET['type']) ? $_GET['type'] : 'all' ));
        return $this->view->render($res, 'admin/post/index.twig', $req->getAttributes());
        // return $res->withJson($this->postModel->getPostByGroupId($group_id));
    }
}
