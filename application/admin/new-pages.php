<?php
$app->group('/admin/pages', function () {

    /*
     * Post Page List
     */
    $this->get('/list[/{type}]', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['pages'=>'all']);
        $req = $req->withAttribute('pages_tab', (isset($args['type']) ? $args['type'] : 'all'));
        $groups = $req->getAttribute('current_group_data');
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.group_id=:group_id
                and pages.status not like '1' and pages.status not like '2' and pages.deleted=:is_deleted
            group by pages.id
            order by pages.id desc
        ");
        $select->bindValue(':group_id', $groups['id'], PDO::PARAM_INT);
        $sub_page = (isset($args['type']) ? $args['type'] : 'all');
        switch($sub_page){
            case 'deleted':
                $select->bindValue(':is_deleted', 1, PDO::PARAM_INT);
                break;
            case 'all':
            default:
                $select->bindValue(':is_deleted', 0, PDO::PARAM_INT);
                break;
        }
        $select->execute();
        $data = [];
        foreach($select->fetchAll(PDO::FETCH_ASSOC) as $post){
            $post['author'] = $this->db->query("select nickname from users where id='".$post['author']."'")->fetchColumn();
            array_push($data, $post);
        }
        $req = $req->withAttribute('pages', $data);
        //        return $res->withJson($data);
        return $this->view->render($res, 'admin/pages.html', $req->getAttributes());
    })->setName('getAdminPagesListHTML');

    /*
     * Post Page Add
     */
    $this->get('/add', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['pages'=>'add']);
        return $this->view->render($res, 'admin/add-pages.html', $req->getAttributes());
    })->setName('getAdminPagesAddHTML');

    /*
     * Post Page edit
     */
    $this->get('/edit[/{id}]', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['pages'=>'l']);
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.id = :id
        ");
        $select->bindParam(':id', $args['id'], PDO::PARAM_INT);
        if($select->execute()){
            $post_data = $select->fetch(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('pages_data', $post_data);
        }
        $select_autosave = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.title_url=:id_autosave
        ");
        $select_autosave->bindValue(':id_autosave', $args['id'].'-autosave', PDO::PARAM_STR);
        if($select_autosave->execute()){
            $post_autosave = $select_autosave->fetch(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('pages_data_autosave', $post_autosave);
        }
        $select_revisions = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.title_url=:id_revisions
            ORDER BY
                pages.id DESC
        ");
        $select_revisions->bindValue(':id_revisions', $args['id'].'-revision', PDO::PARAM_STR);
        if($select_revisions->execute()){
            $revisions = [];
            foreach($select_revisions->fetchAll(PDO::FETCH_ASSOC) as $rev){
                $rev['authors'] = $this->db->query("select nickname, image from users where id='".$rev['author']."'")->fetch(PDO::FETCH_ASSOC);
                array_push($revisions, $rev);
            }
            $req = $req->withAttribute('pages_data_revisions', $revisions);
        }
        return $this->view->render($res, 'admin/edit-pages.html', $req->getAttributes());
    })->setName('getAdminPagesEditHTML');

    /*
     * Post Page version diferent
     */
    $this->get('/diff[/{id}]', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['post'=>'diff']);
        $select = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.id = :id
        ");
        $select->bindParam(':id', $args['id'], PDO::PARAM_INT);
        if($select->execute()){
            $post_data = $select->fetch(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('pages_data', $post_data);
            $post_data['authors'] = $this->db->query("select nickname, image from users where id='".$post_data['author']."'")->fetch(PDO::FETCH_ASSOC);
        }
        $select_revisions = $this->db->prepare("
            SELECT
                *
            FROM
                pages
            WHERE
                pages.title_url=:id_revisions
            ORDER BY
                pages.id asc
        ");
        $select_revisions->bindValue(':id_revisions', $args['id'].'-revision', PDO::PARAM_STR);
        if($select_revisions->execute()){
            $revisions = [];
            $revs = $select_revisions->fetchAll(PDO::FETCH_ASSOC);
            if(array_key_exists(0, $revs)){
                $rev = $revs[0];
                $rev['authors'] = $this->db->query("select nickname, image from users where id='".$req->getAttribute('pages_data')['author']."'")->fetch(PDO::FETCH_ASSOC);
                $prevContent = strip_tags($rev['content']);
                $currentContent = strip_tags($req->getAttribute('pages_data')['content']);
                $rev['titleRevToMain'] = $this->diff->render($rev['title'], $req->getAttribute('pages_data')['title']);
                $rev['contentRevToTitle'] = $this->diff->render($prevContent, $currentContent);
                $revisions[0] = $rev;
            }
            if(array_key_exists(1, $revs)){
                $rev = $revs[1];
                $rev['authors'] = $this->db->query("select nickname, image from users where id='".$rev['author']."'")->fetch(PDO::FETCH_ASSOC);
                $prevContent = strip_tags($revs[0]['content']);
                $currentContent = strip_tags($rev['content']);
                $rev['titleRevToMain'] = $this->diff->render($revs[0]['title'], $rev['title']);
                $rev['contentRevToTitle'] = $this->diff->render($prevContent, $currentContent);
                $revisions[1] = $rev;
            }
            if(array_key_exists(2, $revs)){
                $rev = $revs[2];
                $rev['authors'] = $this->db->query("select nickname, image from users where id='".$rev['author']."'")->fetch(PDO::FETCH_ASSOC);
                $prevContent = strip_tags($revs[1]['content']);
                $currentContent = strip_tags($rev['content']);
                $rev['titleRevToMain'] = $this->diff->render($revs[1]['title'], $rev['title']);
                $rev['contentRevToTitle'] = $this->diff->render($prevContent, $currentContent);
                $revisions[2] = $rev;
            }
            $req = $req->withAttribute('pages_data_revisions', $revisions);
        }
        return $this->view->render($res, 'admin/diff-pages.html', $req->getAttributes());
    })->setName('getAdminPagesDiffHTML');

    $this->get('/revert[/{id}[/{rev_id}]]', function ($req, $res, $args) {
        $revisions = $this->db->query("select title, content from pages where id='".$args['rev_id']."'")->fetch(PDO::FETCH_ASSOC);
        $update = $this->db->prepare("
            update pages
            set author=:author, date=:date, title=:title, title_url=:title_url, content=:content
            where id=:id
        ");
        $user_id = $this->session->user_id;
        $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
        $update->bindValue(':author', $user_id, PDO::PARAM_INT);
        $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
        $update->bindValue(':title', $revisions['title'], PDO::PARAM_STR);
        $update->bindValue(':title_url', $this->slug->make($revisions['title']), PDO::PARAM_STR);
        $update->bindValue(':content', $revisions['content'], PDO::PARAM_STR);
        if($update->execute()){
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminPagesEditHTML', ['id'=>$args['id']]));
        }else{
            $res = $res->withJson([
                'status'=>'publish-autosaved failed',
                'id'=>$args['id']
            ]);
        }
    })->setName('getAdminPagesRevertRevisions302');

    /*
     * Post Actions - change status type
     * - change to published
     * - change to draft
     * - change to trash (temporary delete)
     * - permanent delete
     */
    $this->post('/change[/{id}[/{action}]]', function ($req, $res, $args) {
        switch($args['action']){
            case 'trash':
            case 'revert':
                $update = $this->db->prepare('update pages set deleted=:status where id=:id');
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                $update->bindValue(':status', ($args['action'] == 'trash' ? 1 : 0), PDO::PARAM_INT);
                if($update->execute()){
                    $update = $this->db->prepare('update pages set deleted=:status where title_url like :title_url');
                    $update->bindValue(':title_url', $args['id'].'-autosave%', PDO::PARAM_STR);
                    $update->bindValue(':status', ($args['action'] == 'trash' ? 1 : 0), PDO::PARAM_INT);
                    if($update->execute()){
                        $res = $res->withJSON(['success'=>true]);
                    }else{
                        $res = $res->withJSON([
                            'success'=>false
                        ]);
                    }
                }
                break;
            case 'delete':
                //                $update = $this->db->prepare('update post set deleted=:status where id=:id');
                //                $update->bindParam(':id', $args['id'], PDO::PARAM_INT);
                //                $update->bindValue(':status', 0, PDO::PARAM_INT);
                $res = $res->withJSON(['success'=>false]);
                break;
        }
        return $res;
    })->setName('postAdminPagesChangeJSON');

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
            $descriptions = $_POST['descriptions'];
            $user_id = $this->session->user_id;
            $id = $args['id'];
            $type = $_POST['type'];
            $group_id = $req->getAttribute('current_group_data')['id'];
            switch($type){
                case 'autosave':
                    $id = $this->db->query("select id from pages where title_url like '".$args['id']."-autosave%'")->fetchColumn();
                    $update = $this->db->prepare("
                        update pages
                        set
                            date=:date, author=:author, title=:title, title_url=:title_url,
                            content=:content, description=:description, status=:status
                        where id=:id
                    ");
                    $update->bindValue(':id', $id, PDO::PARAM_INT);
                    $update->bindValue(':author', $user_id, PDO::PARAM_INT);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $args['id'].'-autosave', PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':description', $descriptions, PDO::PARAM_INT);
                    $update->bindValue(':status', 1, PDO::PARAM_INT);
                    if($update->execute()){
                        $res = $res->withJson([
                            'status'=>'autosaved',
                            'id'=>$args['id']
                        ]);
                    }else{
                        $res = $res->withJson([
                            'status'=>'autosave failed',
                            'id'=>$args['id']
                        ]);
                    }
                    break;
                case 'saved':
                    $update = $this->db->prepare("
                        update pages
                        set
                            date=:date, author=:author, title=:title, title_url=:title_url,
                            content=:content, description=:description, status=:status
                        where id=:id
                    ");
                    $update->bindValue(':id', $id, PDO::PARAM_INT);
                    $update->bindValue(':author', $user_id, PDO::PARAM_INT);
                    $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                    $update->bindValue(':title', $title, PDO::PARAM_STR);
                    $update->bindValue(':title_url', $this->slug->make($title), PDO::PARAM_STR);
                    $update->bindValue(':content', $content, PDO::PARAM_STR);
                    $update->bindValue(':description', $descriptions, PDO::PARAM_INT);
                    $update->bindValue(':status', 0, PDO::PARAM_INT);
                    if($update->execute()){
                        $autosaveid = $this->db->query("select id from pages where title_url like '".$args['id']."-autosave%'")->fetchColumn();
                        $update = $this->db->prepare("
                            update pages
                            set
                                date=:date, author=:author, title=:title, title_url=:title_url,
                                content=:content, description=:description, status=:status
                            where id=:id
                        ");
                        $update->bindValue(':id', $autosaveid, PDO::PARAM_INT);
                        $update->bindValue(':author', $user_id, PDO::PARAM_INT);
                        $update->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                        $update->bindValue(':title', $title, PDO::PARAM_STR);
                        $update->bindValue(':title_url', $args['id'].'-autosave-done', PDO::PARAM_STR);
                        $update->bindValue(':content', $content, PDO::PARAM_STR);
                        $update->bindValue(':description', $descriptions, PDO::PARAM_INT);
                        $update->bindValue(':status', 1, PDO::PARAM_INT);
                        if($update->execute()){
                        }
                        $revisions = $this->db->query("select count(id) from pages where status='2' and title_url like '".$args['id']."-revision'")->fetchColumn();
                        if($revisions < 3){
                            $insert = $this->db->prepare("
                                insert into pages
                                (group_id, date, author, title, title_url, content, description, status, deleted)
                                values(:group_id, :date, :author, :title, :title_url, :content, :description, :status, 0)
                            ");
                            $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                            $insert->bindValue(':author', $user_id, PDO::PARAM_INT);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':description', $descriptions, PDO::PARAM_INT);
                            $insert->bindValue(':status', 2, PDO::PARAM_INT);
                            if($insert->execute()){
                            }
                        }else{
                            $oldRevId = $this->db->query("SELECT pages.id FROM pages WHERE pages.status='2' and pages.title_url LIKE '".$id."-revision' ORDER BY pages.id ASC LIMIT 1")->fetchColumn();
                            if($this->tags->clean($oldRevId)){
                                $this->db->exec("delete from pages where id='".$oldRevId."'");
                            }
                            $insert = $this->db->prepare("
                                insert into pages
                                (group_id, date, author, title, title_url, content, description, status, deleted)
                                values(:group_id, :date, :author, :title, :title_url, :content, :description, :status, 0)
                            ");
                            $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                            $insert->bindValue(':author', $user_id, PDO::PARAM_INT);
                            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                            $insert->bindValue(':title', $title, PDO::PARAM_STR);
                            $insert->bindValue(':title_url', $id.'-revision', PDO::PARAM_STR);
                            $insert->bindValue(':content', $content, PDO::PARAM_STR);
                            $insert->bindValue(':description', $descriptions, PDO::PARAM_INT);
                            $insert->bindValue(':status', 2, PDO::PARAM_INT);
                            if($insert->execute()){
                                $revisionid = $this->db->lastInsertId();
                            }

                        }
                        $res = $res->withJson([
                            'status'=>'saved',
                            'id'=>$args['id']
                        ]);
                    }else{
                        $res = $res->withJson([
                            'status'=>'save failed',
                            'id'=>$id
                        ]);
                    }
                    break;
            }
        }else{
            $title = $_POST['title'];
            $content = $_POST['content'];
            $descriptions = $_POST['descriptions'];
            $user_id = $this->session->user_id;
            $group_id = $req->getAttribute('current_group_data')['id'];
            $insert = $this->db->prepare("
                insert into pages
                (group_id, date, author, title, title_url, content, description, status, deleted)
                values(:group_id, :date, :author, :title, :title_url, :content, :description, :status, 0)
            ");
            $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
            $insert->bindValue(':author', $user_id, PDO::PARAM_INT);
            $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
            $insert->bindValue(':title', $title, PDO::PARAM_STR);
            $insert->bindValue(':title_url', $this->slug->make($title), PDO::PARAM_STR);
            $insert->bindValue(':content', $content, PDO::PARAM_STR);
            $insert->bindValue(':description', $descriptions, PDO::PARAM_INT);
            $insert->bindValue(':status', 0, PDO::PARAM_INT);
            if($insert->execute()){
                $postid = $this->db->lastInsertId();
                $insert = $this->db->prepare("
                insert into pages
                (group_id, date, author, title, title_url, content, description, status, deleted)
                values(:group_id, :date, :author, :title, :title_url, :content, :description, :status, 0)
            ");
                $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                $insert->bindValue(':author', $user_id, PDO::PARAM_INT);
                $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $insert->bindValue(':title', $title, PDO::PARAM_STR);
                $insert->bindValue(':title_url', $postid.'-autosave', PDO::PARAM_STR);
                $insert->bindValue(':content', $content, PDO::PARAM_STR);
                $insert->bindValue(':description', $descriptions, PDO::PARAM_INT);
                $insert->bindValue(':status', 0, PDO::PARAM_INT);
                if($insert->execute()){
                    $autosave_id = $this->db->lastInsertId();
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
    })->setName('postAdminPagesSaveJSON');
})->add(function ($req, $res, $next) {
    $select = $this->db->query("select date from pages where group_id='".$req->getAttribute('current_group_data')['id']."' and status='0' and deleted = '0' group by MONTH(date), year(date)")->fetchAll(PDO::FETCH_ASSOC);
    $req = $req->withAttribute('mw_date_groups', $select);
    $res = $next($req, $res);
    return $res;
})->add(function ($req, $res, $next) {
    $group_id = $req->getAttribute('current_group_data')['id'];
    $all_post = $this->db->query("select count(id) from pages where group_id='".$group_id."' and status='0' and deleted = '0'")->fetchColumn();
    $trashed_post = $this->db->query("select count(id) from pages where group_id='".$group_id."' and status='0' and deleted = '1'")->fetchColumn();
    $req = $req->withAttribute('mw_count_all', $all_post);
    $req = $req->withAttribute('mw_count_deleted', $trashed_post);
    $res = $next($req, $res);
    return $res;
})->add($session);
