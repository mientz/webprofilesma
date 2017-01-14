<?php
namespace Wijaya\WebApp\Models;

class PSB extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
    }

    public function getPSBForm($id=null){
        if($id==null){
            $psbForm = $this->pdo->select()->from('psb')->where('deleted', '=', 0)->execute()->fetchAll();
            foreach ($psbForm as &$value) {
                $value['messages'] = json_decode($value['messages'], true);
            }
            return $psbForm;
            unset($value);
        }else{
            $psbForm =  $this->pdo->select()->from('psb')->where('id', '=', $id)->execute()->fetch();
            $psbForm['messages'] = json_decode($psbForm['messages'], true);
            return $psbForm;
        }
    }

    public function getPSBData($psb_id, $regnumber = null){
        if($regnumber == null){
            $psbData = $this->pdo->select()->from('psb_value')->where('psb_id', '=', $psb_id)->execute()->fetchAll();
            foreach ($psbData as &$value) {
                $value['data'] = json_decode($value['data'], true);
            }
            return $psbData;
            unset($value);
        }else{
            $psbData = $this->pdo->select()->from('psb_value')->where('psb_id', '=', $psb_id)->where('regnumber', '=', $regnumber)->execute()->fetch();
            $psbData['data'] = json_decode($psbData['data'], true);
            return $psbData;
        }
    }

    public function getPSBDataCount($psb_id){
        return $this->pdo->select(['count(id)'])->from('psb_value')->where('psb_id', '=', $psb_id)->execute()->fetchColumn();
    }

    public function getPSBDataRegNumber($id){
        return $this->pdo->select(['regnumber'])->form('psb_value')->where('id', '=', $id)->execute()->fetchColumn();
    }

    public function setPSBForm($data, $id=null){
        if($id == null){
            return $this->pdo->insert(array_keys($data))->into('psb')->values(array_values($data))->execute(true);
        }else{
            return $this->pdo->update($data)->table('psb')->where('id', '=', $id)->execute();
        }
    }

    public function setPSBData($data, $regnumber=null){
        if($regnumber == null){
            return $this->pdo->insert(array_keys($data))->into('psb_value')->values(array_values($data))->execute(true);
        }else{
            return $this->pdo->update($data)->table('psb_value')->where('regnumber', '=', $regnumber)->execute();
        }
    }
}
