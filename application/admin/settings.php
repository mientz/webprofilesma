<?php
$app->group('/admin/settings', function () {

    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['settings'=>true]);
        $settings = [];
        foreach($this->db->query("select * from settings")->fetchAll(PDO::FETCH_ASSOC) as $val){
            $settings[$val['type']] = [];
            foreach(json_decode($val['config']) as $key => $config){
                $settings[$val['type']][$key] = base64_decode($config);
            }
        }
//        $req = $req->withAttribute('settings', $settings);
        return $this->view->render($res, 'admin/settings.html', $req->getAttributes());
    })->setName('getAdminSettingsHTML');

    $this->post('[/{type}]', function ($req, $res, $args) {
        $configs = [];
        foreach( $_POST as $key => $val ) {
            $configs[$key] = base64_encode($val);
        }
        $update = $this->db->prepare("update settings set config=:config where type=:type");
        $update->bindValue(':type', $args['type'], PDO::PARAM_STR);
        $update->bindValue(':config', json_encode($configs), PDO::PARAM_STR);
        if($update->execute()){
            $res = $res->withJson([
                'success'=>true,
                'type'=>$args['type']
            ]);
        }else{
            $res = $res->withJson([
                'success'=>false,
                'type'=>$args['type']
            ]);
        }
        return $res;
    })->setName('postAdminSettingsJSON');
})->add($session);
