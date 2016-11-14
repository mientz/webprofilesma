<?php
$app->group('/admin/json', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/category', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        $select = $this->db->prepare("select * from post_category where group_id=:group_id and deleted='0' group by id");
        $select->bindParam(':group_id', $req->getAttributes()["current_group_data"]["group_id"], PDO::PARAM_INT);
        $select->execute();
        $res = $res->withJson($select->fetchAll(PDO::FETCH_NAMED));
        return $res;
    })->setName('admin-category-json');
})->add($session);
