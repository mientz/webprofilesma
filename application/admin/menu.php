<?php
$app->group('/admin/menu', function () {
    /**
     * [[api generate json data for ajax]]
     */
    $this->get('/list', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['view'=>'menu']);
        $group_id = $req->getAttribute('current_group_data')['id'];

        $menus = $this->db->query("select meta from menus where group_id='".$group_id."'")->fetchColumn();
        $req = $req->withAttribute('menus', json_decode($menus));
        //pages
        $pages = $this->db->query("select * from pages where group_id='".$group_id."' and status='0' and deleted='0'");
        $req = $req->withAttribute('pages', $pages);
        //post category
        $category = $this->db->query("select * from post_category where group_id='".$group_id."' and deleted='0'");
        $req = $req->withAttribute('category', $category);

        return $this->view->render($res, 'admin/menu.html', $req->getAttributes());
    })->setName('getAdminMenuListHTML');

    $this->post('/list', function ($req, $res, $args) {
        $group_id = $req->getAttribute('current_group_data')['id'];
        if(isset($_POST['menu_data'])){
            $delete = $this->db->prepare("delete from menus where group_id=:group_id");
            $delete->bindValue(':group_id', $group_id, PDO::PARAM_INT);
            if($delete->execute()){
                $insert = $this->db->prepare("
                    insert into menus(group_id, meta)
                    values(:group_id, :meta)
                ");
                $insert->bindValue(':group_id', $group_id, PDO::PARAM_INT);
                $insert->bindValue(':meta', $_POST['menu_data'], PDO::PARAM_STR);
                if($insert->execute()){
                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminMenuListHTML'));
                }
            }
        }
//        return $this->view->render($res, 'admin/menu.html', $req->getAttributes());
    })->setName('postAdminMenuList302');

})->add($session);
