<?php
$app->get('/postmedia-thumbnail/{img}', function ($req, $res, $args) {
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
})->setName('postmedia-thumbnail');

$app->get('/postmedia/{img}', function ($req, $res, $args) {
    $zoom = (isset($_GET['zoom']) || $_GET['zoom'] < 1 ? $_GET['zoom'] : 1);
    $x = (isset($_GET['x']) ? $_GET['x'] : 0);
    $y = (isset($_GET['y']) ? $_GET['y'] : 0);
    $imgData = $this->manager->make('public/content/'.$args['img'])->exif();
    $res = $res->withHeader('Content-type', $imgData['MimeType']);
    $img = $this->manager->make('public/content/'.$args['img']);
    $img->resize($img->width()*$zoom, $img->height()*$zoom);
    $img->crop(342, 213, $x, $y);
    $res->write($img->response('png'));
    return $res;
})->setName('postmedia');

$app->get('/test', function ($req, $res, $args) {
    $authors = json_decode($this->db->query("select author from post where id='24'")->fetchColumn(), true);
    foreach($authors as $author){
        echo $author['date'];
    }
//    return $res;
})->setName('aa');
