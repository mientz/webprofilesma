<?php
$app->group('/admin/old-page', function () {
    /**
     * [[dashboard Page]]
     */
    $this->get('/all', function ($req, $res, $args) {
        $req = $req->withAttribute('page', 'active');
        $req = $req->withAttribute('page_all', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                deleted ='0' and group_id=:group_id  order by id desc
        ");
        $select->bindParam(':group_id', $groups['id'], PDO::PARAM_INT);
        $select->execute();
        $data = [];
        foreach($select->fetchAll(PDO::FETCH_ASSOC) as $post){
            $authors_data = json_decode($post['author'], true);
            $authors = [];
            foreach($authors_data as $author){
                $name = $this->db->query("select nickname from users where id='".$author['author']."'")->fetchColumn();
                $author['author'] = $name;
                array_push($authors, $author);
            }
            $post['author'] = $authors;
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);
        return $this->view->render($res, 'admin/pages.html', $req->getAttributes());
    })->setName('admin-allpage');

    $this->get('/trash', function ($req, $res, $args) {
        $req = $req->withAttribute('page', 'active');
        $req = $req->withAttribute('page_trash', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                deleted ='1' and group_id=:group_id  order by id desc
        ");
        $select->bindParam(':group_id', $groups['id'], PDO::PARAM_INT);
        $select->execute();
        $data = [];
        foreach($select->fetchAll(PDO::FETCH_ASSOC) as $post){
            $authors_data = json_decode($post['author'], true);
            $authors = [];
            foreach($authors_data as $author){
                $name = $this->db->query("select nickname from users where id='".$author['author']."'")->fetchColumn();
                $author['author'] = $name;
                array_push($authors, $author);
            }
            $post['author'] = $authors;
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);
        return $this->view->render($res, 'admin/pages.html', $req->getAttributes());
    })->setName('admin-allpage-trash');

    $this->get('/add', function ($req, $res, $args) {
        $req = $req->withAttribute('addpage', 'active');
        /*return $res->withJson($req->getAttributes());*/
        return $this->view->render($res, 'admin/add-page.html', $req->getAttributes());
    })->setName('admin-addpage');

    $this->post('/add', function ($req, $res, $args) {
        $title = $_POST['title'];
        $title_url = preg_replace('/\s+/', '-', strtolower($_POST['title']));
        $content = $_POST['content'];
        $description = $_POST['descriptions'];
        $user_id = $this->session->user_id;
        $groups = $req->getAttribute('current_group_data');
        $insert = $this->db->prepare("
            insert into pages
            (group_id, date, author, title, title_url, content, description, deleted)
            values(:group_id, :date, :author, :title, :title_url, :content, :description, 0)
        ");
        $insert->bindParam(':group_id', $groups['id'], PDO::PARAM_INT);
        $insert->bindParam(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
        $insert->bindParam(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $insert->bindParam(':title', $title, PDO::PARAM_STR);
        $insert->bindParam(':title_url', $title_url, PDO::PARAM_STR);
        $insert->bindParam(':content', $content, PDO::PARAM_STR);
        $insert->bindParam(':description', $description, PDO::PARAM_STR);
        if($insert->execute()){
            $postid = $this->db->lastInsertId();
            $res = $res->withJson([
                'status'=>'created',
                'id'=>$postid
            ]);
        }else{
            $res = $res->withJson([
                'status'=>'failed',
                'id'=>$postid
            ]);
        }
        return $res;
    })->setName('admin-addpage-save');

    $this->post('/update/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $title = $_POST['title'];
        $title_url = preg_replace('/\s+/', '-', strtolower($_POST['title']));
        $content = $_POST['content'];
        $description = $_POST['descriptions'];
        $user_id = $this->session->user_id;
        $groups = $req->getAttribute('current_group_data');
        $authors = json_decode($this->db->query("select author from pages where id='".$id."'")->fetchColumn(), true);
        array_push($authors, ["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]);
        $insert = $this->db->prepare("
            update pages
            set group_id=:group_id, date=:date, author=:author, title=:title, title_url=:title_url, content=:content, description=:description
            where id=:id
        ");
        $insert->bindParam(':id', $id, PDO::PARAM_INT);
        $insert->bindParam(':group_id', $groups['id'], PDO::PARAM_INT);
        $insert->bindParam(':author', json_encode($authors), PDO::PARAM_STR);
        $insert->bindParam(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $insert->bindParam(':title', $title, PDO::PARAM_STR);
        $insert->bindParam(':title_url', $title_url, PDO::PARAM_STR);
        $insert->bindParam(':content', $content, PDO::PARAM_STR);
        $insert->bindParam(':description', $description, PDO::PARAM_STR);
        if($insert->execute()){
            $res = $res->withJson([
                'status'=>'created',
                'id'=>$id
            ]);
        }else{
            $res = $res->withJson([
                'status'=>'failed',
                'id'=>$id
            ]);
        }
        return $res;
    })->setName('admin-addpage-update');

    $this->get('/edit/{id}', function ($req, $res, $args) {
        $req = $req->withAttribute('addpost', 'active');
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                id = :id
        ");
        $select->bindParam(':id', $args['id'], PDO::PARAM_INT);
        if($select->execute()){
            $post_data = $select->fetch(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('page_data', $post_data);
        }
        return $this->view->render($res, 'admin/edit-page.html', $req->getAttributes());
    })->setName('admin-addpage-edit');

    $this->post('/change[/{id}[/{action}]]', function ($req, $res, $args) {
        if($args['action']=='trash'){
            $update = $this->db->prepare('update pages set deleted=:status where id=:id');
            $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
            $update->bindValue(':status', 1, PDO::PARAM_INT);
            if($update->execute()){
                $res->withJSON([
                    'success'=>true
                ]);
            }else{
                $res->withJSON([
                    'success'=>false
                ]);
            }
        }elseif($args['action']=='revert'){
            $update = $this->db->prepare('update pages set deleted=:status where id=:id');
            $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
            $update->bindValue(':status', 0, PDO::PARAM_INT);
            if($update->execute()){
                $res->withJSON([
                    'success'=>true
                ]);
            }else{
                $res->withJSON([
                    'success'=>false
                ]);
            }
        }elseif($args['action']=='delete'){
            $update = $this->db->prepare('update pages set deleted=:status where id=:id');
            $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
            $update->bindValue(':status', 2, PDO::PARAM_INT);
            if($update->execute()){
                $res->withJSON([
                    'success'=>true
                ]);
            }else{
                $res->withJSON([
                    'success'=>false
                ]);
            }
        }
        return $res;
    })->setName('admin-page-change');

})->add(function ($req, $res, $next) {
    $all_post = $this->db->query("select count(id) from pages where group_id='".$req->getAttribute('current_group_data')['id']."' and deleted = '0'")->fetchColumn();
    $trashed_post = $this->db->query("select count(id) from pages where group_id='".$req->getAttribute('current_group_data')['id']."' and deleted = '1'")->fetchColumn();
    $req = $req->withAttribute('mw_count_all', $all_post);
    $req = $req->withAttribute('mw_count_deleted', $trashed_post);
    $res = $next($req, $res);
    return $res;
})->add($session);
