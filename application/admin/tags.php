<?php
$app->group('/admin/tags', function () {
    $this->get('', function ($req, $res, $args) {
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
    })->setName('getAdminTagsListJSON');
});
