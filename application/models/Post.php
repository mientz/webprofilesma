<?php
namespace Wijaya\WebApp\Models;

class Post extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $categoryModel;
    protected $tagsModel;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->categoryModel = new \Wijaya\WebApp\Models\Category($ci);
        $this->tagsModel = new \Wijaya\WebApp\Models\Tags($ci);
    }

    public function getPosts(){
        return $this->pdo->select()->from('post')->where('deleted', '<>', 2)->whereBetween('status', [1,2])->execute()->fetchAll();
    }

    public function getPostById($post_id){
        return $this->pdo->select()->from('post')->where('id', '=', $post_id)->execute()->fetch();
    }

    public function getPostAutosave($post_id){
        return $this->pdo->select()->from('post')->whereLike('title_url', $post_id.'-autosave%')->execute()->fetch();
    }

    public function getPostRevisions($post_id){
        return $this->pdo->select()->from('post')->whereLike('title_url', $post_id.'-revisions')->execute()->fetchAll();
    }

    public function getPostRevisionById($revision_id){
        return $this->pdo->select()->from('post')->where('id', '=', $revision_id)->execute()->fetch();
    }

    public function getPostByCategoryId($category_id){
        return $this->pdo->select()->from('post')->where('category_id', '=', $category_id)->whereBetween('status', [1,2])->execute()->fetchAll();
    }

    public function getPostTags(){
        return $this->pdo->select()->from('post_tags')->execute()->fetchAll();
    }

    public function getPostByGroupId($group_id){
        $categories = $this->categoryModel->getCategoryByGroupId($group_id);
        foreach ($categories as &$category) {
            $category['post'] = $this->getPostByCategoryId($category['id']);
        }
        return $categories;
        unset($category);
    }

    public function setPostTags($post_id, $tags_id){
        return $this->pdo->insert(['post_id', 'tag_id'])->into('post_tags')->values([$post_id, $tags_id])->execute();
    }

    public function removePostTags($post_id){
        return $this->pdo->delete()->form('post_tags')->where('post_id', '=', $post_id)->execute();
    }

    public function setPost($data, $id = null){
        if($id == null){
            return $this->pdo->insert(array_keys($data))->into('post')->values(array_values($data))->execute(true);
        }else{
            return $this->pdo->update($data)->table('post')->where('id', '=', $id)->execute();
        }
    }

}
