<?php
class PSB extends WebApp {
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
    public function setPSB($data, $id = null){
        if($id != null){
            return $this->pdo->update($data)->table('psb')->where('id', '=', $id)->execute();
        }else{
            return $this->pdo->insert(array_keys($data))->into('psb')->values(array_values($data))->execute(true);
        }
    }
    public function getPSB($id = null){
        if($id != null){
            return $this->pdo->select()->from('psb')->where('id', '=', $id)->execute()->fetch();
        }else{
            return $this->pdo->select()->from('psb')->execute()->fetchAll();
        }
    }
    public function displayPSBList($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['psb'=>'all']);
        $req = $req->withAttribute('forms', $this->getPSB());
        return $this->view->render($res, 'admin/psb/index.html', $req->getAttributes());
    }
    public function actionsNewPSB($req, $res, $args){
        $data = [
            'name' => $_POST['name'],
            'type' => $_POST['type'],
            'start' => date("Y-m-d", strtotime($_POST['start'])),
            'end' => date("Y-m-d", strtotime($_POST['end'])),
        ];
        if($this->setPSB($data)){
            $res = $res->withJson(['success'=>true, 'messages'=>'Invite Users']);
        }else{
            $res = $res->withJson(['success'=>false, 'messages'=>'Formulir Gagal Tersimpan']);
        }
        return $res;
    }
}
