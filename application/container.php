<?php

/**
 * [[Database connection container]]
 * @param callback $container [[container parameter]]
 */
$container['db'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['database_name'], $settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

/**
 * [[Twig templating container]]
 * @param callback $container [[container parameter]]
 */
$container['view'] = function ($container) {
    $settings = $container->get('settings')['template'];
    if ($settings['cache']) {
        $view = new \Slim\Views\Twig('template', [
            'cache' => $settings['cache_location']
        ]);
    }else{
        $view = new \Slim\Views\Twig('template');
    }
    $view->addExtension(new Twig_Extensions_Extension_Date());
    $view->addExtension(new \Slim\Views\TwigExtension(
        $container['router'],
        $container['request']->getUri()
    ));
    return $view;
};

/*
 * session
 */
$container['session'] = function ($c) {
    return new \SlimSession\Helper;
};

/*
 * Flash message register
 */
$container['flash'] = function () {
    return new \Slim\Flash\Messages();
};

/*
 * Flash message register
 */
$container['manager'] = function () {
    return new \Intervention\Image\ImageManager(array('driver' => 'gd'));
};

/*
 * cogpower text diff plugin register
 */
$container['diff'] = function () {
    return new Qazd\TextDiff;
};

/*
 * Sluger
 */
$container['slug'] = function ($string){
    return new Slug($string);
};

/*
 * files size converter
 */
$container['filesize'] = function ($string){
    return new FileSize($string);
};

/*
 * tags input container
 */
$container['tags'] = function ($container){
    return new Tags($container);
};

class Slug {
    public $string;
    function __construct($string) {
        $this->string = $string;
    }
    public function make($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
    public function file($string) {
        return strtolower(trim(preg_replace('~[^0-9a-z\.]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
    }
}

class FileSize {
    public $bytes;
    function __construct($bytes) {
        $this->bytes = $bytes;
    }
    public function convert($bytes){
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );
        foreach($arBytes as $arItem){
            if($bytes >= $arItem["VALUE"]){
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}

class Tags {
    public $db;
    public $slug;
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



