<?php
$app->group('/admin/api', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/category', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        $select = $this->db->prepare("select * from post_category where group_id=:group_id and deleted='0' and group_id=:group_id group by id");
        $select->bindParam(':group_id', $req->getAttributes()["current_group_data"]["id"], PDO::PARAM_INT);
        $select->execute();
        $res = $res->withJson($select->fetchAll(PDO::FETCH_NAMED));
        return $res;
    })->setName('admin-category-json');

    $this->post('/category', function ($req, $res, $args) {
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
    })->setName('admin-add-category');
    /**
     * [[api post media uploader]]
     */
//    $this->post('/postmedia', function ($req, $res, $args) {
//        if(isset($_FILES["file"])){
//            if ($_FILES["file"]["type"] == "image/bmp" || $_FILES["file"]["type"] == "image/png" || $_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/gif"){
//                // read image from temporary file
//                $img = $this->manager->make($_FILES['file']['tmp_name']);
//                $thumb = $this->manager->make($_FILES['file']['tmp_name']);
//                $ext = end((explode(".", $_FILES['file']['name'])));
//                $sourcePath = $_FILES['file']['tmp_name'];
//                $file = 'post-media'.date("-Y-m-d-H-i-s-").$req->getAttribute('current_group_data')['id'].'-'.rand();
//                $image_path = "public/content/".$file.'.'.$ext;
//                $thumb_path = "public/content/".$file.'-thumbnail.'.$ext;
//                $img->save($image_path);
//            }
//        }
//        return $res;
//    })->setName('admin-postmedia-uploader');
//
//    $this->get('/postmedia/delete/{img}', function ($req, $res, $args) {
//        if (file_exists('public/content/'.$args['img'])){
//            $res = $res->withJson([
//                "result" => true,
//                "cause" => ""
//            ]);
//            unlink('public/content/'.$args['img']);
//        }else{
//            $res = $res->withJson([
//                "result" => false,
//                "cause" => "File tidak ditemukan"
//            ]);
//        }
//        return $res;
//    })->setName('admin-postmedia-delete');

    /**
     * [[api list all post media]]
     */
//    $this->get('/postmedia', function ($req, $res, $args) {
//        function FileSizeConvert($bytes){
//            $bytes = floatval($bytes);
//            $arBytes = array(
//                0 => array(
//                    "UNIT" => "TB",
//                    "VALUE" => pow(1024, 4)
//                ),
//                1 => array(
//                    "UNIT" => "GB",
//                    "VALUE" => pow(1024, 3)
//                ),
//                2 => array(
//                    "UNIT" => "MB",
//                    "VALUE" => pow(1024, 2)
//                ),
//                3 => array(
//                    "UNIT" => "KB",
//                    "VALUE" => 1024
//                ),
//                4 => array(
//                    "UNIT" => "B",
//                    "VALUE" => 1
//                ),
//            );
//            foreach($arBytes as $arItem){
//                if($bytes >= $arItem["VALUE"]){
//                    $result = $bytes / $arItem["VALUE"];
//                    $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
//                    break;
//                }
//            }
//            return $result;
//        }
//
//        $path = 'public/content';
//        $files = array_diff(scandir($path, 1), array('.', '..'));
//        $json = [];
//        $array_time = [];
//        foreach($files as $file){
//            $file_size = FileSizeConvert(filesize('public/content/'.$file));
//            $img = $this->manager->make('public/content/'.$file);
//            $subs = strpos($file, 'thumbnail');
//            if($subs === false){
//                $image_data = explode("-", $file);
//                if($image_data[8] == $req->getAttribute('current_group_data')['id']){
//                    if(!array_key_exists($image_data[2], $array_time)){
//                        $array_time[$image_data[2]] = [];
//                    }
//                    if(!in_array($image_data[3], $array_time[$image_data[2]])){
//                        array_push($array_time[$image_data[2]], $image_data[3]);
//                    }
//                    if($_GET['year'] == $image_data[2] || $_GET['year'] == 'all'){
//                        if($_GET['month'] == $image_data[3] || $_GET['month'] == 'all'){
//                            array_push($json, [
//                                "year" => $image_data[2],
//                                "month" => $image_data[3],
//                                "day" => $image_data[4],
//                                "pixel" => $img->width().' x '.$img->width(),
//                                "file" => $file,
//                                "thumb" => $this->router->pathFor('postmedia-thumbnail', ['img'=>$file]),
//                                "size" => $file_size
//                            ]);
//                        }
//                    }
//                }
//            }
//        }
//        $res = $res->withJson([
//            "image" => $json,
//            "time" =>$array_time
//        ]);
//        return $res;
//
//
//    })->setName('admin-postmedia-list');

    $this->get('/tags', function ($req, $res, $args) {
        /*
        * list all tags
        */
        $select = $this->db->query("select name from tags")->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach($select as $tag){
            array_push($data, $tag['name']);
        }
        $res = $res->withJson($data);
        return $res;
    })->setName('admin-tags-json');

    $this->get('/test', function ($req, $res, $args) {
        $res = $res->withJson($req->getAttribute('current_group_data'));
        return $res;
    })->setName('admin-tags-json');


})->add($session);
