<?php
$app->group('/admin/post', function () {
    /**
     * [[post page]]
     */
    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost');

    /**
     * add new post page
     */
    $this->get('/add', function ($req, $res, $args) {
        $req = $req->withAttribute('addpost', 'active');

        return $this->view->render($res, 'admin/add-post.html', $req->getAttributes());
    })->setName('admin-addpost');

    $this->post('/add', function ($req, $res, $args) {
        $id = $this->db->query("select IFNULL(max(id), 0) from post")->fetchColumn(PDO::FETCH_ASSOC)+1;
        $title = $_POST['title'];
        $title_url = $_POST['title'];
        preg_replace('/\W+/', '-', strtolower($title_url));
        $content = $_POST['content'];
        $visibility = $_POST['visibility'];
        $password = $_POST['password'];
        $category = $_POST['category'];
        $tag = $_POST['tag'];
        $status = ( $_POST['status'] == 'draft' ? '0' : '1' );
        $image = $_POST['image'];
        $user_id = $this->session->user_id;
        $insert = $this->db->prepare("
            insert into post
            (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
            values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
        ");
        $insert->bindParam(':categorry_id', $category, PDO::PARAM_INT);
        $insert->bindParam(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
        $insert->bindParam(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $insert->bindParam(':title', $title, PDO::PARAM_STR);
        $insert->bindParam(':title_url', $title_url, PDO::PARAM_STR);
        $insert->bindParam(':content', $content, PDO::PARAM_STR);
        $insert->bindParam(':visibility', $visibility, PDO::PARAM_INT);
        $insert->bindParam(':password', md5($password), PDO::PARAM_STR);
        $insert->bindParam(':status', $status, PDO::PARAM_INT);
        $insert->bindParam(':header_image', $image, PDO::PARAM_STR);
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
    })->setName('admin-addpost-save');

    $this->post('/update/{id}', function ($req, $res, $args) {
        $id = $args['id'];
        $title = $_POST['title'];
        $title_url = $_POST['title'];
        preg_replace('/\W+/', '-', strtolower($title_url));
        $content = $_POST['content'];
        $visibility = $_POST['visibility'];
        $password = $_POST['password'];
        $category = $_POST['category'];
        $tag = $_POST['tag'];
        $status = ( $_POST['status'] == 'draft' ? '0' : '1' );
        $image = $_POST['image'];
        $user_id = $this->session->user_id;
        $authors = json_decode($this->db->query("select author from post where id='24'")->fetchColumn(), true);
        array_push($authors, ["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]);
        $update = $this->db->prepare("
            update post
            set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
            where id=:id
        ");
        $update->bindParam(':id', $id, PDO::PARAM_INT);
        $update->bindParam(':categorry_id', $category, PDO::PARAM_INT);
        $update->bindParam(':author', json_encode($authors), PDO::PARAM_STR);
        $update->bindParam(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $update->bindParam(':title', $title, PDO::PARAM_STR);
        $update->bindParam(':title_url', $title_url, PDO::PARAM_STR);
        $update->bindParam(':content', $content, PDO::PARAM_STR);
        $update->bindParam(':visibility', $visibility, PDO::PARAM_INT);
        $update->bindParam(':password', md5($password), PDO::PARAM_STR);
        $update->bindParam(':status', $status, PDO::PARAM_INT);
        $update->bindParam(':header_image', $image, PDO::PARAM_STR);
        if($update->execute()){
            $res = $res->withJson([
                'status'=>'Updated',
                'id'=>$id
            ]);
        }else{
            $res = $res->withJson([
                'status'=>'failed',
                'id'=>$id
            ]);
        }
    })->setName('admin-addpost-update');
})->add($session);
