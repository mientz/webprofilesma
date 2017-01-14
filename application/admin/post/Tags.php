<?php
namespace Wijaya\WebApp\Admin;
class Tags extends \Wijaya\WebApp{

    protected $ci;
    protected $user_id;
    protected $group_id;

    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->user_id = $this->session->user_id;
        $this->group_id = $this->getUserGroupData($this->user_id, $this->session->groupKey)['id'];
    }

    public function getTags($post_id){
        $tags = [];
        $tags_select = $this->pdo->select(['tags.name'])->from('tags')->join('post_tags', 'tags.id', '=', 'post_tags.tag_id')->where('post_tags.post_id', '=', $post_id)->execute()->fetchAll();
        foreach($tags_select as $tag){
            array_push($tags, $tag['name']);
        }
        return $tags;
    }

    public function setTags($post_id, $tagString){
        $deleteTags = $this->pdo->delete()->from('post_tags')->where('post_id', '=', $post_id)->execute();
        $result = false;
        $tags = explode(',', $tagString);
        foreach($tags as $tag){
            $isTagExist = $this->pdo->select([ 'id' ])->count('id', 'total')->from('tags')->where('name', '=', $tag)->execute()->fetch();
            if($isTagExist['total'] == 0){
                $newTag = $this->pdo->insert([ 'name', 'name_url' ])->into('tags')->values($tag, $this->slug->make($tag))->execute(true);
                if($newTag){
                    $newPostTag = $this->pdo->insert([ 'post_id', 'tag_id' ])->into('post_tags')->value([ $post_id, $newTag ])->execute();
                    $result = $newPostTag;
                }
            }else{
                $newPostTag = $this->pdo->insert(['post_id', 'tag_id'])->into('post_tags')->values([ $post_id, $isTagExist['id'] ])->execute();
                $result = $newPostTag;
            }
        }
        return $result;
    }
}
