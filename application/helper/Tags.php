<?php
class Tags {
    private $db;
    private $slug;
    function __construct($container) {
        $this->db = $container->get('db');
        $this->slug = $container->get('slug');
    }
    public function set($id, $tags){
        $this->db->exec("delete from post_tags where post_id='".$id."'");
        foreach($tags as $tag){
            $is_tagexist = $this->db->prepare("select id from tags where name='".$tag."'");
            $is_tagexist->execute();
            if($is_tagexist->rowCount() == 0){
                $insert = $this->db->prepare("insert into tags(name, name_url) values(:name, :name_url)");
                $insert->bindValue(':name', $tag, PDO::PARAM_INT);
                $insert->bindValue(':name_url', $this->slug->make($tag), PDO::PARAM_INT);
                if($insert->execute()){
                    $insert = $this->db->prepare("insert into post_tags(post_id, tag_id) values('".$id."', '".$this->db->lastInsertId()."')");
                    $insert->execute();
                }
            }else{
                $tags_id = $is_tagexist->fetchColumn();
                $is_postexist = $this->db->query("select count(id) from post_tags where post_id='".$id."' and tag_id='".$tags_id."'")->fetchColumn();
                if($is_postexist == 0){
                    $insert = $this->db->prepare("insert into post_tags(post_id, tag_id) values('".$id."', '".$tags_id."')");
                    $insert->execute();
                }
            }
        }
        return true;
    }

    public function clean($id){
        $this->db->exec("delete from post_tags where post_id='".$id."'");
        return true;
    }

    public function get($post_id){
        $tags = $this->db->query("select tags.name, tags.id from tags, post_tags where tags.id=post_tags.tag_id and post_tags.post_id='".$post_id."'")->fetchAll(PDO::FETCH_ASSOC);
        return $tags;
    }
}
