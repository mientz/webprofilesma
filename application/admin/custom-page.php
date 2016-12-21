<?php
$app->group('/admin/custom', function () {

    $this->get('/teachers', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['pages'=>'teachers']);
        $req = $req->withAttribute('customTeachers', 'active');
        $select = $this->db->query("select * from teachers order by id desc")->fetchAll(PDO::FETCH_ASSOC);
        $req = $req->withAttribute('teachers', $select);
        return $this->view->render($res, 'admin/teachers.html', $req->getAttributes());
    })->setName('getAdminCustomPageTeachersHTML');

    $this->post('/teachers[/{actions}[/{id}]]', function ($req, $res, $args) {
        $actions = (isset($args['actions']) ? $args['actions'] : 'add');
        switch($actions){
            case 'edit':
                $update = $this->db->prepare("
                    update teachers
                        set name=:name, field=:field, number=:number, quote=:quote, type=:type, photo=:photo
                    where
                        id=:id
                ");
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                $update->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
                $update->bindValue(':field', $_POST['field'], PDO::PARAM_STR);
                $update->bindValue(':number', $_POST['number'], PDO::PARAM_STR);
                $update->bindValue(':quote', $_POST['quote'], PDO::PARAM_STR);
                $update->bindValue(':type', $_POST['type'], PDO::PARAM_STR);
                $update->bindValue(':photo', $_POST['photo'], PDO::PARAM_STR);
                if($update->execute()){
                    $res = $res->withJson(["success"=>true]);
                }
                break;
            case 'delete':
                $update = $this->db->prepare("
                    delete from teachers
                    where id=:id
                ");
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                if($update->execute()){
                    $res = $res->withJson(["success"=>true]);
                }
                break;
            case 'add':
            default:
                $insert = $this->db->prepare("
                    insert into
                        teachers(name, field, number, quote, type, photo)
                    values
                        (:name, :field, :number, :quote, :type, :photo)
                ");
                $insert->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
                $insert->bindValue(':field', $_POST['field'], PDO::PARAM_STR);
                $insert->bindValue(':number', $_POST['number'], PDO::PARAM_STR);
                $insert->bindValue(':quote', $_POST['quote'], PDO::PARAM_STR);
                $insert->bindValue(':type', $_POST['type'], PDO::PARAM_STR);
                $insert->bindValue(':photo', $_POST['photo'], PDO::PARAM_LOB);
                if($insert->execute()){
                    $res = $res->withJson(["success"=>true]);
                }
                break;
        }
        return $res;
    })->setName('postAdminCustomPageTeachersJSON');

    $this->get('/gallery[/{album_id}]', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['gallery'=>true]);
        $group_id = $req->getAttribute('current_group_data')['id'];
        if(isset($args['album_id'])){
            $req = $req->withAttribute('list_type', 'photos');
            $req = $req->withAttribute('album', $this->db->query("select * from albums where id='".$args['album_id']."'")->fetch(PDO::FETCH_ASSOC));
            $req = $req->withAttribute('albums', $this->db->query("select id 'value', name 'text' from albums where group_id='".$group_id."'")->fetchAll(PDO::FETCH_ASSOC));
            $photos = $this->db->query("select * from gallery where album_id='".$args['album_id']."'")->fetchAll(PDO::FETCH_ASSOC);
            $req = $req->withAttribute('photos', $photos);
        }else{
            $albums = [];
            foreach($this->db->query("select * from albums where group_id='".$group_id."' and deleted='0'")->fetchAll(PDO::FETCH_ASSOC) as $album){
                $album['photos']=$this->db->query("select * from gallery where album_id='".$album['id']."'")->fetchAll(PDO::FETCH_ASSOC);
                array_push($albums, $album);
            }
            $req = $req->withAttribute('list_type', 'albums');
            $req = $req->withAttribute('albums', $albums);
        }
        return $this->view->render($res, 'admin/gallery.html', $req->getAttributes());
    })->setName('getAdminCustomGalleryHTML');

    $this->post('/gallery/actions[/{type}[/{id}]]', function ($req, $res, $args) {
        $user_id = $this->session->user_id;
        $group_id = $req->getAttribute('current_group_data')['id'];
        switch($args['type']){
            case 'albums-add':
                $name = $_POST['name'];
                $insert = $this->db->prepare("
                    insert into albums(group_id, name, date)
                    values(:group_id, :name, :date)
                ");
                $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                $insert->bindValue(':name', $name, PDO::PARAM_STR);
                $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                if($insert->execute()){
                    $id = $this->db->lastInsertId();
                    $res = $res->withJson([
                        'success'=>true,
                        'id'=>$id
                    ]);
                }
                break;
            case 'photo-add':
                if(isset($_FILES["file"])){
                    if ($_FILES["file"]["type"] == "image/bmp" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/gif"){
                        $filename = $this->slug->file($_FILES['file']['name']);
                        $img = $this->manager->make($_FILES['file']['tmp_name']);
                        if(file_exists("public/gallery/".$filename)){
                            $filename = rand().$filename;
                        }
                        $name = str_replace(".".end((explode(".", $filename))),"",$filename);
                        $image_path = "public/gallery/".$filename;
                        $insert = $this->db->prepare("
                            insert into gallery(album_id, title, file, date)
                            values(:album_id, :title, :file, :date)
                        ");
                        $insert->bindValue(':album_id', $args['id'], PDO::PARAM_INT);
                        $insert->bindValue(':title', $name, PDO::PARAM_STR);
                        $insert->bindValue(':file', $filename, PDO::PARAM_STR);
                        $insert->bindValue(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                        if($insert->execute()){
                            $img->save($image_path);
                            $id = $this->db->lastInsertId();
                            $res = $res->withJson([
                                "success" => true,
                                "file"=>$filename,
                                "id"=>$id
                            ]);
                        }else{
                            $res = $res->withJson([
                                "failed" => true
                            ]);
                        }
                    }else{
                        $res = $res->withJson([
                            "failed" => true
                        ]);
                    }
                }else{
                    $res = $res->withJson([
                        "failed" => true
                    ]);
                }
                break;
            case 'album-to-public':
                if($this->db->exec("update albums set visibility='1' where id='".$args['id']."'")){
                    $res = $res->withJson([
                        "success" => true
                    ]);
                }
                break;
            case 'album-to-hidden':
                if($this->db->exec("update albums set visibility='0' where id='".$args['id']."'")){
                    $res = $res->withJson([
                        "success" => true
                    ]);
                }
                break;
            case 'album-rename':
                $update = $this->db->prepare("update albums set name=:name where id=:id");
                $update->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                if($update->execute()){
                    $res = $res->withJson([
                        "success" => true
                    ]);
                }
                break;
            case 'album-delete':
                    if($this->db->exec("update albums set deleted='1' where id='".$args['id']."'")){
                        $res = $res->withJson([
                            "success" => true
                        ]);
                    }
                break;
            case 'photo-move':
                $update = $this->db->prepare("update gallery set album_id=:album_id where id=:id");
                $update->bindValue(':album_id', $_POST['albumid'], PDO::PARAM_STR);
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                if($update->execute()){
                    $res = $res->withJson([
                        "success" => true
                    ]);
                }
                break;
            case 'photo-rename':
                $update = $this->db->prepare("update gallery set title=:name where id=:id");
                $update->bindValue(':name', $_POST['name'], PDO::PARAM_STR);
                $update->bindValue(':id', $args['id'], PDO::PARAM_INT);
                if($update->execute()){
                    $res = $res->withJson([
                        "success" => true
                    ]);
                }
                break;
            case 'photo-delete':
                if($this->db->exec("delete from gallery where id='".$args['id']."'")){
                        $res = $res->withJson([
                            "success" => true
                        ]);
                    }
                break;
        }
        return $res;
    })->setName('postAdminCustomGalleryJSON');

})->add($session);
