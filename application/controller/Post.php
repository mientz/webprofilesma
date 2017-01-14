<?php
namespace Wijaya\WebApp\Controller;

class Post extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $userModel;
    protected $postModel;
    protected $tagsModel;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->userModel = new \Wijaya\WebApp\Models\Users($ci);
        $this->postModel = new \Wijaya\WebApp\Models\Post($ci);
        $this->tagsModel = new \Wijaya\WebApp\Models\Tags($ci);
    }

    public function getPost($req, $res, $args){
        if(array_key_exists('id', $args)){
            return $res->withJson($this->postModel->getPostById($args['id']));
        }elseif(isset($_GET['group'])){
            return $res->withJson($this->postModel->getPostByGroupId($_GET['group']));
        }
    }

    public function newPost($req, $res, $args){
        $data = $_POST;
        $tagString = (isset($data['tags']) ? $data['tags'] : null);
        $tags = explode(',', $tag);
        unset($data['tags']);
        $newPost = $this->postModel->setPost($data);
        if($newPost){
            foreach($tags as $tag){
                $this->setPostTags($newPost, $this->tagsModel->getTagsIdByName($tag));
            }
            $res = $res->withJson([])
        }
    }

    public function __invoke($req, $res, $args){
        $method = $req->getMethod();
        switch($method){
            case 'GET':
                return $this->getPost($req, $res, $args);
                break;
            case 'POST':
                return $this->newPost($req, $res, $args);
                break;
        }
    }
}
