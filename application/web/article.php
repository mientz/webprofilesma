<?php
$app->group('/post', function () {
    /**
     * [[get all post list]]
     */
    $this->get('[/{params:.*}]', function ($req, $res, $args) {
        $params = explode('/', $req->getAttribute('params'));

        if ($params[0] == "" || $params[0] == "page"){ /* [[Get all post]] */
            return $this->view->render($res, 'web/posts.html');

        }elseif($params[0] == "category"){ /* [[get post by category]] */
            return $this->view->render($res, 'web/posts.html');

        }elseif(preg_match('/^[0-9]+$/', $params[0])){ /* [[get post by date posted]] */
            return $this->view->render($res, 'web/posts.html');

        }else{ /* [[give detailed post]] */
            return $this->view->render($res, 'web/post-details.html');
        }
    })->add($allpost)->setName('post');
});
