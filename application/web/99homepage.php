<?php
$app->group('/', function () {
    $this->get('', function ($req, $res, $args) {
    return $this->view->render($res, 'web/homepage.html');
    })->setName('homepage');

    $this->get('{year:[0-9]+}/{month:[0-9]+}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/posts.html');
    })->setName('getPostByDateHTML');

    $this->get('{category}/{post:.+}.{ext:html}', function ($req, $res, $args) {
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.header_image, post.content
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post_category.name_url=:category_name and post.title_url=:post_title
            group by post.id
            order by post.id desc
        ");
        $select->bindValue(':category_name', $args['category'], PDO::PARAM_STR);
        $select->bindValue(':post_title', $args['post'], PDO::PARAM_STR);
        if($select->execute()){
            $data_post = $select->fetch(PDO::FETCH_ASSOC);
            $data_post['authors'] = $this->db->query("select nickname from users where id='".$data_post['author']."'")->fetchColumn();
            $data_post['tags'] = $this->tags->get($data_post['id']);
            $req = $req->withAttribute('post', $data_post);
        }
        return $this->view->render($res, 'web/post-details.html', $req->getAttributes());
    })->setName('getPostDetailHTML');

    $this->get('{category}{ext:/}', function ($req, $res, $args) {
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.header_image, post.content
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post_category.name_url=:category_name
            group by post.id
            order by post.id desc
        ");
        $select->bindValue(':category_name', $args['category'], PDO::PARAM_STR);
        if($select->execute()){
            $data_post = [];
            foreach($select->fetchAll(PDO::FETCH_ASSOC) as $data){
                $data['authors'] = $this->db->query("select nickname from users where id='".$data['author']."'")->fetchColumn();
                $data['tags'] = $this->tags->get($data['id']);
                array_push($data_post, $data);
            }
            $req = $req->withAttribute('posts', $data_post);
        }
        $req = $req->withAttribute('category', $this->db->query("select name from post_category where name_url='".$args['category']."'")->fetchColumn());
        return $this->view->render($res, 'web/posts.html', $req->getAttributes());
    })->setName('getPostByCategoryHTML');

    $this->get('{page:.+}.{ext:html}', function ($req, $res, $args) {

        return $this->view->render($res, 'web/page.html');
    })->setName('page');

    $this->get('page/{id}', function ($req, $res, $args) {
        return $this->view->render($res, 'web/homepage.html');
    })->setName('custom-page');

});
