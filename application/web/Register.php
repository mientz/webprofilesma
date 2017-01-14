<?php
class Register extends Web {
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
    public function getPSB($id = null){
        if($id != null){
            return $this->pdo->select()->from('psb')->where('id', '=', $id)->execute()->fetch();
        }
    }
    public function getPSBData($id = null){
        if($id != null){
            return $this->pdo->select()->from('psb_data')->where('id', '=', $id)->execute()->fetch();
        }
    }
    public function setPSBData($data, $id = null){
        if($id != null){
            return $this->pdo->update($data)->table('psb_data')->where('id', '=', $id)->execute();
        }else{
            return $this->pdo->insert(array_keys($data))->into('psb_data')->values(array_values($data))->execute(true);
        }
    }
    public function displayRegister($req, $res, $args){
        $req = $req->withAttribute('psb_id', $args['id']);
        $req = $req->withAttribute('step', $args['step']);
        if(isset($args['data_id'])){
            $req = $req->withAttribute('data_id', $args['data_id']);
        }
        return $this->view->render($res, 'web/register/index.html', $req->getAttributes());
    }
    public function actionsRegister($req, $res, $args){
        $data = $_POST;
        switch($args['step']){
            case 1:
                $data['psb_id'] = $args['id'];
                $setPSB_data = $this->setPSBData($data);
                if($setPSB_data){
                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('webRegisterReguler', [
                        'step'=>$args['step']+1,
                        'id'=>$args['id'],
                        'data_id' => $setPSB_data
                    ]));
                }
                break;
            case 2:
                if($this->setPSBData($data, $args['data_id'])){
                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('webRegisterReguler', [
                        'step'=>$args['step']+1,
                        'id'=>$args['id'],
                        'data_id' => $args['data_id']
                    ]));
                }
                break;
            case 3:
                return $res->withJson($_POST);
//                if($this->setPSBData($data, $args['data_id'])){
//                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('webRegisterReguler', [
//                        'step'=>$args['step']+1,
//                        'id'=>$args['id'],
//                        'data_id' => $args['data_id']
//                    ]));
//                }
                break;
        }
    }
}