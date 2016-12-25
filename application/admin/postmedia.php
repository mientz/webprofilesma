<?php
$app->group('/admin/postmedia', function () {
    /**
     * [[dashboard Page]]
     */
    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['post'=>'media']);
        return $this->view->render($res, 'admin/postmedia.html', $req->getAttributes());
    })->setName('getAdminPostmediaHTML');

    $this->post('/upload', function ($req, $res, $args) {
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
    })->setName('postAdminPostmediaUploadJSON');

    $this->get('/list', function ($req, $res, $args) {
        $path = 'public/content';
        $json = [];
        $query = "select * from postmedia where group_id=:group_id ";
        $query = $query.($_GET['date'] == 'all' ?" and year(date)<>:year and month(date)<>:month ":" and year(date)=:year and month(date)=:month");
        $query = $query." order by date desc";
        $select = $this->db->prepare($query);
        $select->bindValue(':group_id', $req->getAttribute('current_group_data')['id'], PDO::PARAM_INT);
        $select->bindValue(':year', date("Y", strtotime($_GET['date'])), PDO::PARAM_INT);
        $select->bindValue(':month', date("n", strtotime($_GET['date'])), PDO::PARAM_INT);
        if($select->execute()){
            $file = [];
            foreach($select->fetchAll(PDO::FETCH_ASSOC) as $data){
                $data['dateString'] = date("d F Y", strtotime($data['date']));
                $data['author'] = $this->db->query("select nickname from users where id='".$data['author']."'")->fetchColumn();
                $data['thumbnail'] = $this->router->pathFor('getAdminPostmediaThumbnailIMAGE', ["img"=>$data['file']]);
                array_push($file, $data);
            }
            $json["postmedia"]=$file;
        }
        $select = $this->db->prepare("select date from postmedia where group_id=:group_id group by year(date), month(date)");
        $select->bindParam(':group_id', $req->getAttribute('current_group_data')['id'], PDO::PARAM_INT);
        if($select->execute()){
            $time = [];
            foreach($select->fetchAll(PDO::FETCH_ASSOC) as $data){
                array_push($time,date("F Y", strtotime($data['date'])));
            }
            $json["time"]=$time;
        }
        $res = $res->withJson($json);
        return $res;
    })->setName('getAdminPostmediaListJSON');

    $this->get('/thumbnail[/{img}]', function ($req, $res, $args) {
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
    })->setName('getAdminPostmediaThumbnailIMAGE');

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
    })->setName('getAdminPostmediaExifJSON');

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
    })->setName('getAdminPostmediaDeleteJSON');

    $this->get('/croped[/{img}]', function ($req, $res, $args) {
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
    })->setName('getAdminPostmediaCropedIMAGE');

    $this->get('/cropit[/{params:.*}]', function ($req, $res, $args) {
        if(isset($args['params'])){
            $param = explode('/',$args["params"]);
            $imgData = [];
            $imgData["width"] = $param[0];
            $imgData["height"] = $param[1];
            $imgData["x"] = $param[2];
            $imgData["y"] = $param[3];
            $imgData["zoom"] = $param[4];
            $imgData["image"] = $param[5];
            $res = $res->withJson($imgData);
        }else{
            $res = $res->withJson(["image"=>"no-image"]);
        }
        return $res;
    })->setName('getAdminPostmediaCropitJson');

})->add($session);
