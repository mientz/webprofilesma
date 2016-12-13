<?php

$app->group('/admin/test', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/cropit', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */

        return $this->view->render($res, 'admin/test-cropit.html', $req->getAttributes());
    })->setName('admin-test-cropit');
    $this->get('/cropit/{img}', function ($req, $res, $args) {
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
    })->setName('admin-test-cropit-result');
});
