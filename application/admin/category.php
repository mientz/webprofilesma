<?php
$app->group('/admin/category', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/shorthand-list', function ($req, $res, $args) {
        /*
        * list all category from current group data
        */
        $select = $this->db->prepare("select * from post_category where group_id=:group_id and deleted='0' group by id");
        $select->bindParam(':group_id', $req->getAttributes()["current_group_data"]["id"], PDO::PARAM_INT);
        $select->execute();
        $res = $res->withJson($select->fetchAll(PDO::FETCH_NAMED));
        return $res;
    })->setName('getAdminCategoryListJSON');

    $this->post('/shorthand-insert', function ($req, $res, $args) {
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
    })->setName('postAdminCategoryShorthandInsertJSON');

})->add($session);
