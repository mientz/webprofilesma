<?php
$app->group('/admin/post', function () {

    /*
     * Post Page List
     */
    $this->get('/list[/{type}]', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['post'=>'all']);
        $req = $req->withAttribute('post_tab', (isset($args['type']) ? $args['type'] : 'all'));
        $groups = $req->getAttribute('current_group_data');
        $select = $this->db->prepare("
            SELECT
                post.id, post_category.`name` 'category', post_category.name_url, post.author, post.`status`, post.title, post.title_url, post.visibility
            FROM
                post as post,
                post_category
            WHERE
                post.category_id = post_category.id and post_category.group_id=:group_id
                and post.status not like '2' and post.status not like '4' and post.status not like '5'
                and post.deleted <> '2' and post.deleted=:is_deleted and post.status<>:is_status
            group by post.id
            order by post.id desc
        ");
        $select->bindValue(':group_id', $groups['id'], PDO::PARAM_INT);
        $sub_page = (isset($args['type']) ? $args['type'] : 'all');
        switch($sub_page){
            case 'deleted':
                $select->bindValue(':is_deleted', 1, PDO::PARAM_INT);
                $select->bindValue(':is_status', 0, PDO::PARAM_INT);
                break;
            case 'published':
                $select->bindValue(':is_deleted', 0, PDO::PARAM_INT);
                $select->bindValue(':is_status', 1, PDO::PARAM_INT);
                break;
            case 'draft':
                $select->bindValue(':is_deleted', 0, PDO::PARAM_INT);
                $select->bindValue(':is_status', 3, PDO::PARAM_INT);
                break;
            default:
                $select->bindValue(':is_deleted', 0, PDO::PARAM_INT);
                $select->bindValue(':is_status', 0, PDO::PARAM_INT);
                break;
        }
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
            $post['tags'] = $this->tags->get($post['id']);
            array_push($data, $post);
        }
        $req = $req->withAttribute('posts', $data);
//        return $res->withJson($data);
        return $this->view->render($res, 'admin/post.html', $req->getAttributes());
    })->setName('getAdminPostListHTML');



    $this->post('/change[/{id}[/{action}]]', function ($req, $res, $args) {
        switch($args['action']){
            case 'draft':
            case 'publish':
                $update = $this->db->prepare('update post set status=:status where id=:id');
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                $update->bindValue(':status', ($args['action'] == 'draft' ? 1 : 3), PDO::PARAM_INT);
                if($update->execute()){
                    $update = $this->db->prepare('update post set status=:status where title_url like :title_url');
                    $update->bindValue(':title_url', $args['id'].'-autosave%', PDO::PARAM_STR);
                    $update->bindValue(':status', ($args['action'] == 'draft' ? 2 : 4), PDO::PARAM_INT);
                    if($update->execute()){
                        $res->withJSON(['success'=>true]);
                    }else{
                        $res->withJSON(['success'=>false]);
                    }
                }
                break;
            case 'trash':
            case 'revert':
                $update = $this->db->prepare('update post set deleted=:status where id=:id');
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                $update->bindValue(':status', ($args['action'] == 'trash' ? 1 : 0), PDO::PARAM_INT);
                if($update->execute()){
                    $update = $this->db->prepare('update post set deleted=:status where title_url like :title_url');
                    $update->bindValue(':title_url', $args['id'].'-autosave%', PDO::PARAM_STR);
                    $update->bindValue(':status', ($args['action'] == 'trash' ? 1 : 0), PDO::PARAM_INT);
                    if($update->execute()){
                        $res->withJSON(['success'=>true]);
                    }else{
                        $res->withJSON([
                            'success'=>false
                        ]);
                    }
                }
                break;
            case 'delete':
//                $update = $this->db->prepare('update post set deleted=:status where id=:id');
//                $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
//                $update->bindValue(':status', 0, PDO::PARAM_INT);
                $res->withJSON(['success'=>false]);
                break;
        }
    })->setName('postAdminPostChangeJSON');

    /*
     * post action
     * - 0 'new' create new post to db and create the first autosave version of post
     * - 1 'draft-autosave' automaticaly save the post data to draft backup
     * - 2 'draft-saved' save the post as draft
     * - 3 'publish-autosave' automaticaly save the post data to published backup
     * - 4 'publish-saved' publish the post
     * - 5 'revision' save 3 revision of the post
     */
    $this->post('/save[/{id}]', function ($req, $res, $args) {
        if(isset($args['id'])){
            $title = $_POST['title'];
            $content = $_POST['content'];
            $visibility = $_POST['visibility'];
            $password = $_POST['password'];
            $category = $_POST['category'];
            $tag = $_POST['tag'];
            $image = $_POST['image'];
            $user_id = $this->session->user_id;
            $id = $args['id'];
            $type = $_POST['type'];
            switch($type){
                case 'draft-autosave':
                    $id = $this->db->query("select id from post where status='2' and title_url like '".$args['id']."-autosave"."%'")->fetchColumn();
                    $update = $this->db->prepare("
                        update post
                        set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                        where id=:id
                    ");
                    $update->bindValue(':id', $id, PDO::PARAM_INT);
                    $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                    $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $args['id'].'-autosave', PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                    $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                    $update->bindValue(':status', 2, PDO::PARAM_INT);
                    $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                    if($update->execute()){
                        $res = $res->withJson([
                            'status'=>'draft-autosaved',
                            'id'=>$args['id']
                        ]);
                        $this->tags->set($id, $tags = explode(',', $tag));
                    }else{
                        $res = $res->withJson([
                            'status'=>'draft-autosave failed',
                            'id'=>$args['id']
                        ]);
                    }
                    break;
                case 'draft-saved':
                case 'Simpan Konsep':
                    $update = $this->db->prepare("
                        update post
                        set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                        where id=:id
                    ");
                    $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                    $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                    $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $this->slug->make($title), PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                    $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                    $update->bindValue(':status', 1, PDO::PARAM_INT);
                    $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                    if($update->execute()){
                        $this->tags->set($id, $tags = explode(',', $tag));
                        $autosaveid = $this->db->query("select id from post where status='2' and title_url like '".$args['id']."'-autosave%")->fetchColumn();
                        $update = $this->db->prepare("
                            update post
                            set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                            where id=:id
                        ");
                        $update->bindValue(':id', $autosaveid, PDO::PARAM_INT);
                        $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                        $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                        $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                        $update->bindValue(':title', $title, PDO::PARAM_STR);
                        $update->bindValue(':title_url', $id.'-autosave-done', PDO::PARAM_STR);
                        $update->bindValue(':content', $content, PDO::PARAM_STR);
                        $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                        $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                        $update->bindValue(':status', 2, PDO::PARAM_INT);
                        $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                        if($update->execute()){
                            $this->tags->set($autosaveid, $tags = explode(',', $tag));
                        }
                        $revisions = $this->db->query("select count(id) from post where status='5' and title_url like '".$args['id']."'-revision");
                        if($revisions <= 3){
                            $insert = $this->db->prepare("
                                insert into post
                                (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                                values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
                            ");
                            $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                            $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                            $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
                            $insert->bindValue(':status', 5, PDO::PARAM_INT);
                            $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
                            if($insert->execute()){
                                $revisionid = $this->db->lastInsertId();
                                $this->tags->set($revisionid, $tags = explode(',', $tag));
                            }
                        }else{
                            $oldRevId = $this->db->query("SELECT post.id FROM post WHERE post.title_url LIKE '".$id."-revision' ORDER BY post.id ASC LIMIT 1")->fetchColumn();
                            if($this->tags->clean($oldRevId)){
                                $this->db->exec("delete from post where id='".$oldRevId."'");
                            }
                            $insert = $this->db->prepare("
                                insert into post
                                (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                                values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
                            ");
                            $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                            $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                            $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
                            $insert->bindValue(':status', 5, PDO::PARAM_INT);
                            $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
                            if($insert->execute()){
                                $revisionid = $this->db->lastInsertId();
                                $this->tags->set($revisionid, $tags = explode(',', $tag));
                            }

                        }
                        $res = $res->withJson([
                            'status'=>'draft-saved',
                            'id'=>$args['id']
                        ]);
                    }else{
                        $res = $res->withJson([
                            'status'=>'draft-save failed',
                            'id'=>$id
                        ]);
                    }
                    break;
                case 'publish-autosave':
                    $id = $this->db->query("select id from post where status='2' and title_url like '".$args['id']."'-autosave%")->fetchColumn();
                    $update = $this->db->prepare("
                        update post
                        set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                        where id=:id
                    ");
                    $update->bindValue(':id', $id, PDO::PARAM_INT);
                    $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                    $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $args['id'].'-autosave', PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                    $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                    $update->bindValue(':status', 4, PDO::PARAM_INT);
                    $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                    if($update->execute()){
                        $res = $res->withJson([
                            'status'=>'publish-autosaved',
                            'id'=>$args['id']
                        ]);
                        $this->tags->set($id, $tags = explode(',', $tag));
                    }else{
                        $res = $res->withJson([
                            'status'=>'publish-autosaved failed',
                            'id'=>$args['id']
                        ]);
                    }
                    break;
                case 'publish-saved':
                case 'Terbitkan':
                    $update = $this->db->prepare("
                        update post
                        set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                        where id=:id
                    ");
                    $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                    $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                    $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $this->slug->make($title), PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                    $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                    $update->bindValue(':status', 3, PDO::PARAM_INT);
                    $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                    if($update->execute()){
                        $this->tags->set($id, $tags = explode(',', $tag));
                        $autosaveid = $this->db->query("select id from post where status='2' and title_url like '".$args['id']."-autosave%'")->fetchColumn();
                        $update = $this->db->prepare("
                            update post
                            set category_id=:categorry_id, author=:author, date=:date, title=:title, title_url=:title_url, content=:content, visibility=:visibility, password=:password, status=:status, header_image=:header_image
                            where id=:id
                        ");
                        $update->bindValue(':id', $autosaveid, PDO::PARAM_INT);
                        $update->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                        $update->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                        $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                        $update->bindValue(':title', $title, PDO::PARAM_STR);
                        $update->bindValue(':title_url', $id.'-autosave-done', PDO::PARAM_STR);
                        $update->bindValue(':content', $content, PDO::PARAM_STR);
                        $update->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                        $update->bindValue(':password', md5($password), PDO::PARAM_STR);
                        $update->bindValue(':status', 4, PDO::PARAM_INT);
                        $update->bindValue(':header_image', $image, PDO::PARAM_STR);
                        if($update->execute()){
                            $this->tags->set($autosaveid, $tags = explode(',', $tag));
                        }
                        $revisions = $this->db->query("select count(id) from post where status='5' and title_url like '".$args['id']."-revision'")->fetchColumn();
                        if($revisions <= 3){
                            $insert = $this->db->prepare("
                                insert into post
                                (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                                values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
                            ");
                            $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                            $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                            $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
                            $insert->bindValue(':status', 5, PDO::PARAM_INT);
                            $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
                            if($insert->execute()){
                                $revisionid = $this->db->lastInsertId();
                                $this->tags->set($revisionid, $tags = explode(',', $tag));
                            }
                        }else{
                            $oldRevId = $this->db->query("SELECT post.id FROM post WHERE post.title_url LIKE '".$id."-revision' ORDER BY post.id ASC LIMIT 1")->fetchColumn();
                            if($this->tags->clean($oldRevId)){
                                $this->db->exec("delete from post where id='".$oldRevId."'");
                            }
                            $insert = $this->db->prepare("
                                insert into post
                                (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                                values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
                            ");
                            $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                            $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                            $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
                            $insert->bindValue(':status', 5, PDO::PARAM_INT);
                            $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
                            if($insert->execute()){
                                $revisionid = $this->db->lastInsertId();
                                $this->tags->set($revisionid, $tags = explode(',', $tag));
                            }
                        }
                        $res = $res->withJson([
                            'status'=>'publish-saved',
                            'id'=>$args['id']
                        ]);
                    }else{
                        $res = $res->withJson([
                            'status'=>'publish-saved failed',
                            'id'=>$args['id']
                        ]);
                    }
                    break;
            }
        }else{
            $title = $_POST['title'];
            $title_url = $this->slug->make($_POST['title']);
            $content = $_POST['content'];
            $visibility = $_POST['visibility'];
            $password = $_POST['password'];
            $category = $_POST['category'];
            $tag = $_POST['tag'];
            $image = $_POST['image'];
            $user_id = $this->session->user_id;
            $insert = $this->db->prepare("
                insert into post
                (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
            ");
            $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
            $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $insert->bindValue(':title', $title, PDO::PARAM_STR);
            $insert->bindValue(':title_url', $title_url, PDO::PARAM_STR);
            $insert->bindValue(':content', $content, PDO::PARAM_STR);
            $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
            $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
            $insert->bindValue(':status', 1, PDO::PARAM_INT);
            $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
            if($insert->execute()){
                $postid = $this->db->lastInsertId();
                $this->tags->set($postid, $tags = explode(',', $tag));
                $insert = $this->db->prepare("
                    insert into post
                    (category_id, author, date, title, title_url, content, visibility, password, status, header_image, deleted)
                    values(:categorry_id, :author, :date, :title, :title_url, :content, :visibility, :password, :status, :header_image, 0)
                ");
                $insert->bindValue(':categorry_id', $category, PDO::PARAM_INT);
                $insert->bindValue(':author', json_encode([["author"=>$user_id, "date"=>date("Y-m-d H:i:s")]]), PDO::PARAM_STR);
                $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $insert->bindValue(':title', $title, PDO::PARAM_STR);
                $insert->bindValue(':title_url', $postid.'-autosave', PDO::PARAM_STR);
                $insert->bindValue(':content', $content, PDO::PARAM_STR);
                $insert->bindValue(':visibility', $visibility, PDO::PARAM_INT);
                $insert->bindValue(':password', md5($password), PDO::PARAM_STR);
                $insert->bindValue(':status', 2, PDO::PARAM_INT);
                $insert->bindValue(':header_image', $image, PDO::PARAM_STR);
                if($insert->execute()){
                    $autosave_id = $this->db->lastInsertId();
                    $this->tags->set($autosave_id, $tags = explode(',', $tag));
                }
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
        }
        return $res;
    })->setName('postAdminPostSaveJSON');
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
    $all_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0' and post.status<>'2' and post.status<>'4' and post.status<>'5'")->fetchColumn();
    $published_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and post.status='3' and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0'")->fetchColumn();
    $draft_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and post.status='1' and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '0'")->fetchColumn();
    $trashed_post = $this->db->query("select count(post.id) from post, post_category, groups where post.category_id=post_category.id and post_category.group_id=groups.id and group_id='".$req->getAttribute('current_group_data')['id']."' and post.deleted = '1' and post.status<>'2' and post.status<>'4' and post.status<>'5'")->fetchColumn();
    $req = $req->withAttribute('mw_count_all', $all_post);
    $req = $req->withAttribute('mw_count_published', $published_post);
    $req = $req->withAttribute('mw_count_draft', $draft_post);
    $req = $req->withAttribute('mw_count_deleted', $trashed_post);
    $res = $next($req, $res);
    return $res;
})->add($session);
