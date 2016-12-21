<?php
$app->group('/admin/category', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/shorthand-list', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        $select = $this->db->prepare("select * from post_category where group_id=:group_id and deleted='0' group by id");
        $select->bindParam(':group_id', $req->getAttributes()["current_group_data"]["id"], PDO::PARAM_INT);
        $select->execute();
        $res = $res->withJson($select->fetchAll(PDO::FETCH_NAMED));
        return $res;
    })->setName('getAdminCategoryListJSON');

    $this->post('/shorthand-insert', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        $group_data = $req->getAttribute('current_group_data');
        $name = $_POST['name'];
        $name_url = preg_replace('/\s+/', '-', strtolower($name));;
        $insert = $this->db->prepare("insert into post_category(group_id, name, name_url, deleted) values(:group_id, :name, :name_url, 0)");
        $insert->bindParam(':group_id', $group_data['id'], PDO::PARAM_INT);
        $insert->bindParam(':name', $name, PDO::PARAM_STR);
        $insert->bindParam(':name_url', $name_url, PDO::PARAM_STR);
        if($insert->execute()){
            $res = $res->withJson([
                'inserted'=>true
            ]);
        }else{
            $res = $res->withJson([
                'inserted'=>false
            ]);
        }
        return $res;
    })->setName('postAdminCategoryShorthandInsertJSON');

    $this->get('/list[/{id}]', function ($req, $res, $args) {
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

    $this->post('/edit[/{id}]', function ($req, $res, $args) {
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

    $this->post('/delete/{id}', function ($req, $res, $args) {
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

})->add($session);
