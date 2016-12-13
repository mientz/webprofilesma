<?php
$app->group('/admin/postmedia', function () {
    /**
     * [[dashboard Page]]
     */
    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('postmedia', 'active');
        return $this->view->render($res, 'admin/postmedia.html', $req->getAttributes());
    })->setName('admin-allpostmedia');

    $this->post('', function ($req, $res, $args) {
        if(isset($_FILES["file"])){
            if ($_FILES["file"]["type"] == "image/bmp" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/gif"){
                // read image from temporary file
                $filename = $this->slug->file($_FILES['file']['name']);
                $img = $this->manager->make($_FILES['file']['tmp_name']);
                if(file_exists("public/content/".$filename)){
                    $filename = rand().$filename;
                }
                $name = str_replace(".".end((explode(".", $filename))),"",$filename);
                $image_path = "public/content/".$filename;
                $insert = $this->db->prepare("
                    insert into postmedia(group_id, name, date, author, file, deleted)
                    values(:group_id, :name, :date, :author, :file, '0')
                ");
                $insert->bindParam(':group_id', $req->getAttribute('current_group_data')['id'], PDO::PARAM_INT);
                $insert->bindParam(':author', $user_id = $this->session->user_id, PDO::PARAM_INT);
                $insert->bindParam(':name', $name, PDO::PARAM_STR);
                $insert->bindParam(':date', date("Y-m-d H:i:s"), PDO::PARAM_STR);
                $insert->bindParam(':file', $filename, PDO::PARAM_STR);
                if($insert->execute()){
                    $img->save($image_path);
                    $res = $res->withJson([
                       "success" => true
                    ]);
                }else{
                    $res = $res->withJson([
                        "failed" => true
                    ]);
                }
                $res = $res->withJson([
                    "failed" => true
                ]);
            }
            $res = $res->withJson([
                "failed" => true
            ]);
        }
        $res = $res->withJson([
            "failed" => true
        ]);
//        return $res;
    })->setName('admin-postmedia-uploader');

    $this->get('/list', function ($req, $res, $args) {
        $path = 'public/content';
        $json = [];
        $query = "select * from postmedia where group_id=:group_id ";
        $query = $query.($_GET['year'] == 'all' ?" and year(date)<>:year ":" and year(date)=:year ");
        $query = $query.($_GET['month'] == 'all' ?" and month(date)<>:month ":" and month(date)=:month ");
        $select = $this->db->prepare($query);
        $select->bindParam(':group_id', $req->getAttribute('current_group_data')['id'], PDO::PARAM_INT);
        $select->bindParam(':year', date("Y", strtotime($_GET['year'])), PDO::PARAM_INT);
        $select->bindParam(':month', date("n", strtotime($_GET['month'])), PDO::PARAM_INT);
        if($select->execute()){
            $file = [];
            $time = [];
            foreach($select->fetchAll(PDO::FETCH_ASSOC) as $data){
                $data['dateString'] = date("d F Y", strtotime($data['date']));
                $data['author'] = $this->db->query("select nickname from users where id='".$data['author']."'")->fetchColumn();
                $data['thumbnail'] = $this->router->pathFor('admin-postmedia-thumbnail', ["img"=>$data['file']]);
                array_push($file, $data);
                if(!array_key_exists(date("Y", strtotime($data['date'])), $time)){
                    $time[date("Y", strtotime($data['date']))] = [];
                }
                if(!in_array(date("F", strtotime($data['date'])), $time[date("Y", strtotime($data['date']))])){
                    array_push($time[date("Y", strtotime($data['date']))], date("F", strtotime($data['date'])));
                }
            }
            $res = $res->withJson([
                "postmedia" =>$file,
                "time_group" =>$time
            ]);
        }
        return $res;
    })->setName('admin-postmedia-list');

    $this->get('/thumbnail/{img}', function ($req, $res, $args) {
        $imgData = $this->manager->make('public/content/'.$args['img'])->exif();
        $res = $res->withHeader('Content-type', $imgData['MimeType']);
        $img = $this->manager->make('public/content/'.$args['img']);
        if($img->width() > $img->height()){
            $img->crop($img->width(), $img->width());
        }elseif($img->width() < $img->height()){
            $img->crop($img->height(), $img->height());
        }
        $img->resize(187, 187);
        $res->write($img->response('png'));
        return $res;
    })->setName('admin-postmedia-thumbnail');

    $this->get('/exif[/{id}]', function ($req, $res, $args) {
        $select = $this->db->query("select * from postmedia where id='".$args['id']."'")->fetch(PDO::FETCH_ASSOC);
        $select['author'] = $this->db->query("select nickname from users where id='".$select['author']."'")->fetchColumn();
        $select['dateString'] = $select['dateString'] = date("d F Y", strtotime($select['date']));
        $imgData = $this->manager->make('public/content/'.$select['file'])->exif();
        $imgData["FileSize"] = $this->filesize->convert($imgData["FileSize"]);
        $res = $res->withJson([
            "exif"=>$imgData,
            "postmedia"=>$select
        ]);
        return $res;
    })->setName('admin-postmedia-exif');

    $this->get('/delete[/{id}]', function ($req, $res, $args) {
        if(isset($args['id'])){
            $select = $this->db->prepare("delete from postmedia where id=:id");
            $select->bindParam(':id', $args['id'], PDO::PARAM_INT);
            if($select->execute()){
                $res = $res->withJson([
                    "status"=>"success"
                ]);
            }
        }else{
            $res = $res->withJson([
                "status"=>"failed",
                "cause"=>"ridak ada berkas yang dipilih"
            ]);
        }
        return $res;
    })->setName('admin-postmedia-delete');
})->add($session);
