<?php
class Settings extends WebApp {
    /*
     * This Class Only Variable
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
    }
    public function getSettings(){
        $settings = [];
        foreach($this->pdo->select()->from('settings')->execute()->fetchAll() as $val){
            $settings[$val['type']] = [];
            foreach(json_decode($val['config']) as $key => $config){
                $settings[$val['type']][$key] = base64_decode($config);
            }
        }
        return $settings;
    }
    public function setSettings($type, $config){
        return $this->pdo->update(['config'=>$config])->table('settings')->where('type', '=', $type)->execute();
    }
    public function displaySettings($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['settings'=>true]);
        $req = $req->withAttribute('settings', $this->getSettings());
        return $this->view->render($res, 'admin/settings.html', $req->getAttributes());
    }
    public function ActionsSaveSettings($req, $res, $args) {
        $configs = [];
        foreach( $_POST as $key => $val ) {
            $configs[$key] = base64_encode($val);
        }
        if($this->setSettings($args['type'], json_encode($configs))){
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
    }
}
