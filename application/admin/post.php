<?php
$app->group('/admin/post', function () {
    /**
     * [[post page]]
     */
    $this->get('/all', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');
        $req = $req->withAttribute('post_all', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.visibility,
                (SELECT GROUP_CONCAT(tags.`name`) from tags as tags, post_tags as post_tags where tags.id=post_tags.tag_id and post_tags.post_id = post.id) as tags
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post.deleted ='0' and post_category.group_id=:group_id  order by post.id desc
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
            $post['tags'] = str_replace(',', ', ', $post['tags']);
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost');

    $this->get('/published', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');
        $req = $req->withAttribute('post_published', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        //all post
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.visibility,
                (SELECT GROUP_CONCAT(tags.`name`) from tags as tags, post_tags as post_tags where tags.id=post_tags.tag_id and post_tags.post_id = post.id) as tags
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post.status='1' and post.deleted ='0' and post_category.group_id=:group_id  order by post.id desc
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
            $post['tags'] = str_replace(',', ', ', $post['tags']);
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost-published');

    $this->get('/draft', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');
        $req = $req->withAttribute('post_draft', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        //all post
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.visibility,
                (SELECT GROUP_CONCAT(tags.`name`) from tags as tags, post_tags as post_tags where tags.id=post_tags.tag_id and post_tags.post_id = post.id) as tags
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post.status='0' and post.deleted ='0' and post_category.group_id=:group_id  order by post.id desc
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
            $post['tags'] = str_replace(',', ', ', $post['tags']);
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost-draft');

    $this->get('/trash', function ($req, $res, $args) {
        $req = $req->withAttribute('post', 'active');
        $req = $req->withAttribute('post_trash', 'active');
        $groups = $req->getAttribute('current_group_data');
        //all post
        //all post
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.visibility,
                (SELECT GROUP_CONCAT(tags.`name`) from tags as tags, post_tags as post_tags where tags.id=post_tags.tag_id and post_tags.post_id = post.id) as tags
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post.deleted ='1' and post_category.group_id=:group_id order by post.id desc
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
            $post['tags'] = str_replace(',', ', ', $post['tags']);
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);

        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('admin-allpost-trash');

    /**
     * add new post page
     */
    $this->get('/add', function ($req, $res, $args) {
        $req = $req->withAttribute('addpost', 'active');

        return $this->view->render($res, 'admin/add-post.html', $req->getAttributes());
    })->setName('admin-addpost');

    $this->post('/add', function ($req, $res, $args) {
        $filename = "";
        if(isset($_POST['image'])){
            $img = $this->manager->make($_POST['image'])->encode('png');
            $filename = 'post-header'.date("-Y-m-d-H-i-s-").rand().'-'.rand().'.png';
            $image_path = "public/header/".$filename;
            $img->save($image_path);
            echo $_POST['image'];
        }
        $id = $this->db->query("select IFNULL(max(id), 0) from post")->fetchColumn(PDO::FETCH_ASSOC)+1;
        $title = $_POST['title'];
        $title_url = preg_replace('/\s+/', '-', strtolower($_POST['title']));
        $content = $_POST['content'];
        $visibility = $_POST['visibility'];
        $password = $_POST['password'];
        $category = $_POST['category'];
        $tag = $_POST['tag'];
        $status = ( $_POST['status'] == 'draft' ? '0' : '1' );
        $image = $filename;
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
            $tags = explode(',', $tag);
            $clean_tags = $this->db->exec("delete from post_tags where post_id='".$postid."'");
            foreach($tags as $tag){
                $is_tagexist = $this->db->prepare("select id from tags where name='".$tag."'");
                $is_tagexist->execute();
                if($is_tagexist->rowCount() == 0){
                    $insert = $this->db->prepare("insert into tags(name, name_url) values(:name, :name_url)");
                    $insert->bindParam(':name', $tag, PDO::PARAM_INT);
                    $insert->bindParam(':name_url', $tag = preg_replace('/\s+/', '-', $tag), PDO::PARAM_INT);
                    if($insert->execute()){
                        $insert = $this->db->prepare("insert into post_tags(post_id, tag_id) values('".$postid."', '".$this->db->lastInsertId()."')");
                        $insert->execute();
                    }
                }else{
                    $tags_id = $is_tagexist->fetchColumn();
                    $is_postexist = $this->db->query("select count(id) from post_tags where post_id='".$postid."' and tag_id='".$tags_id."'")->fetchColumn();
                    if($is_postexist == 0){
                        $insert = $this->db->prepare("insert into post_tags(post_id, tag_id) values('".$postid."', '".$tags_id."')");
                        $insert->execute();
                    }
                }
            }
        }else{
            $res = $res->withJson([
                'status'=>'failed',
                'id'=>$postid
            ]);
        }
        return $res;
    })->setName('admin-addpost-save');


    $this->get('/edit/{id}', function ($req, $res, $args) {
        $req = $req->withAttribute('addpost', 'active');
        $select = $this->db->prepare("
            SELECT
                post.id, post.category_id, post.author, post.date, post.title, post.title_url, post.content, post.visibility, post.`password`, post.`status`, post.header_image, post.deleted,
                (SELECT GROUP_CONCAT(tags.`name`) from tags as tags, post_tags as post_tags where tags.id=post_tags.tag_id and post_tags.post_id = post.id) as tags
            FROM
                post as post
            WHERE
                post.id = :id
        ");
        $select->bindParam(':id', $args['id'], PDO::PARAM_INT);
        if($select->execute()){
            $post_data = $select->fetchAll(PDO::FETCH_ASSOC)[0];
            $req = $req->withAttribute('post_data', $post_data);
        }
        return $this->view->render($res, 'admin/edit-post.html', $req->getAttributes());
    })->setName('admin-editpost');

    $this->post('/change[/{id}[/{action}]]', function ($req, $res, $args) {
        if($args['action']=='draft'){
            $update = $this->db->prepare('update post set status=:status where id=:id');
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
        }elseif($args['action']=='publish'){
            $update = $this->db->prepare('update post set status=:status where id=:id');
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
        }elseif($args['action']=='trash'){
            $update = $this->db->prepare('update post set deleted=:status where id=:id');
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
            $update = $this->db->prepare('update post set deleted=:status where id=:id');
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
            $update = $this->db->prepare('update post set deleted=:status where id=:id');
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
    })->setName('admin-change');

    $this->post('/update/{id}', function ($req, $res, $args) {
        $filename = "";
        if(isset($_POST['image'])){
            $img = $this->manager->make($_POST['image'])->encode('png');
            $filename = 'post-header'.date("-Y-m-d-H-i-s-").$args['id'].'-'.rand().'.png';
            $image_path = "public/header/".$filename;
            $img->save($image_path);
            echo $_POST['image'];
        }
        $id = $args['id'];
        $title = $_POST['title'];
        $title_url = preg_replace('/\s+/', '-', strtolower($_POST['title']));
        $content = $_POST['content'];
        $visibility = $_POST['visibility'];
        $password = $_POST['password'];
        $category = $_POST['category'];
        $tag = $_POST['tag'];
        $status = ( $_POST['status'] == 'draft' ? '0' : '1' );
        $image = $filename;
        $user_id = $this->session->user_id;
        $authors = json_decode($this->db->query("select author from post where id='".$id."'")->fetchColumn(), true);
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
            $tags = explode(',', $tag);
            $clean_tags = $this->db->exec("delete from post_tags where post_id='".$id."'");
            foreach($tags as $tag){
                $is_tagexist = $this->db->prepare("select id from tags where name='".$tag."'");
                $is_tagexist->execute();
                if($is_tagexist->rowCount() == 0){
                    $insert = $this->db->prepare("insert into tags(name, name_url) values(:name, :name_url)");
                    $insert->bindParam(':name', $tag, PDO::PARAM_INT);
                    $insert->bindParam(':name_url', $tag = preg_replace('/\s+/', '-', $tag), PDO::PARAM_INT);
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
        }else{
            $res = $res->withJson([
                'status'=>'failed',
                'id'=>$id
            ]);
        }
    })->setName('admin-addpost-update');

    $this->get('/category[/{id}]', function ($req, $res, $args) {
        $group_data = $req->getAttribute('current_group_data');
        $req = $req->withAttribute('category', 'active');
        $default_id = $this->db->query("select id from post_category where name='Tidak Berkategori' and group_id='".$group_data['id']."'")->fetchColumn();
        $select = $this->db->query("
            select
                id, group_id, name, name_url, description,
                (select count(post.id) from post as post where post.category_id=category.id) as post_count
            from post_category as category
            where
                group_id='".$req->getAttribute('current_group_data')['id']."' and id<>'".$default_id."' and deleted = '0'
        ")->fetchAll(PDO::FETCH_ASSOC);
        $req = $req->withAttribute('page_category', $select);
        if(isset($args['id'])){
            $select = $this->db->query("
                select
                    id, group_id, name, name_url, description
                from post_category
                where
                    id='".$args['id']."'
            ")->fetch(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('selected_category', $select);
            $req = $req->withAttribute('is_edit', $args['id']);
        }
        return $this->view->render($res, 'admin/category.html', $req->getAttributes());
    })->setName('admin-allcategory');

    $this->post('/category[/{id}]', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        if(isset($args['id'])){
            $name = $_POST['name'];
            $description = $_POST['description'];
            $name_url = preg_replace('/\s+/', '-', strtolower($name));
            $update = $this->db->prepare("update post_category set name=:name, name_url=:name_url, description=:description where id=:id");
            $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
            $update->bindParam(':name', $name, PDO::PARAM_STR);
            $update->bindParam(':name_url', $name_url, PDO::PARAM_STR);
            $update->bindParam(':description', $description, PDO::PARAM_STR);
            if($update->execute()){
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-allcategory'));
            }
        }else{
            $group_data = $req->getAttribute('current_group_data');
            $name = $_POST['name'];
            $description = $_POST['description'];
            $name_url = preg_replace('/\s+/', '-', strtolower($name));;
            $insert = $this->db->prepare("insert into post_category(group_id, name, name_url, description, deleted) values(:group_id, :name, :name_url, :description, 0)");
            $insert->bindParam(':group_id', $group_data['id'], PDO::PARAM_INT);
            $insert->bindParam(':name', $name, PDO::PARAM_STR);
            $insert->bindParam(':name_url', $name_url, PDO::PARAM_STR);
            $insert->bindParam(':description', $description, PDO::PARAM_STR);
            if($insert->execute()){
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-allcategory'));
            }
        }
    })->setName('admin-allcategory-add');

    $this->post('/category/delete/{id}', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        if(isset($args['id'])){
            $group_data = $req->getAttribute('current_group_data');
            $update = $this->db->prepare("update post_category set deleted='1' where id=:old_id");
            $update->bindParam(':old_id', $args['id'], PDO::PARAM_INT);
            if($update->execute()){
                $select = $this->db->query("select id from post_category where name='Tidak Berkategori' and group_id='".$group_data['id']."'")->fetchColumn();
                $update = $this->db->prepare("update post set category_id=:new_id where category_id=:old_id");
                $update->bindParam(':new_id', $select, PDO::PARAM_INT);
                $update->bindParam(':old_id', $args['id'], PDO::PARAM_INT);
                if($update->execute()){
                    return $res->withJson([
                        'success'=>true
                    ]);
                }
            }
        }
    })->setName('admin-allcategory-delete');

})->add(function ($req, $res, $next) {
    $select = $this->db->query("select * from post_category where group_id='".$req->getAttribute('current_group_data')['id']."' and deleted = '0'")->fetchAll(PDO::FETCH_ASSOC);
    $req = $req->withAttribute('mw_category_list', $select);
    $res = $next($req, $res);
    return $res;
})->add(function ($req, $res, $next) {
    $select = $this->db->query("select post.date from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0' group by MONTH(post.date), year(post.date)")->fetchAll(PDO::FETCH_ASSOC);
    $req = $req->withAttribute('mw_date_groups', $select);
    $res = $next($req, $res);
    return $res;
})->add(function ($req, $res, $next) {
    $all_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0'")->fetchColumn();
    $published_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and post.status='1' and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0'")->fetchColumn();
    $draft_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and post.status='0' and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0'")->fetchColumn();
    $trashed_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '1'")->fetchColumn();
    $req = $req->withAttribute('mw_count_all', $all_post);
    $req = $req->withAttribute('mw_count_published', $published_post);
    $req = $req->withAttribute('mw_count_draft', $draft_post);
    $req = $req->withAttribute('mw_count_deleted', $trashed_post);
    $res = $next($req, $res);
    return $res;
})->add($session);
