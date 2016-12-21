<?php

$app->group('/postmedia', function () {
    $this->get('/header[/{img:.*}]', function ($req, $res, $args) {
        $image = explode('/', $req->getAttribute('img'));
        $imgData = $this->manager->make('public/content/'.$image[5])->exif();
        $res = $res->withHeader('Content-type', $imgData['MimeType']);
        $img = $this->manager->make('public/content/'.$image[5]);
        $img->resize($img->width()*$image[4], $img->height()*$image[4]);
        $img->crop($image[0], $image[1], $image[2], $image[3] );
        $res->write($img->response('png'));
        return $res;
    })->setName('getPostmediaHeaderIMAGE');
});
