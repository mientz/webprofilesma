<?php
namespace Wijaya\WebApp\Models;

class PPDB extends \Wijaya\WebApp {
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

    public function getForm($id=null){
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

    public function findFormByNameUrl($url){
        $id = $this->pdo->select(['id'])->from('psb')->where('name_url', '=', $url)->execute()->fetchColumn();
        return $this->getForm($id);
    }

    public function setForm($data, $id=null){
        if($id == null){
            return $this->pdo->insert(array_keys($data))->into('psb')->values(array_values($data))->execute(true);
        }else{
            return $this->pdo->update($data)->table('psb')->where('id', '=', $id)->execute();
        }
    }

    public function getData($form_id, $id=null){
        if($id == null){
            $psbData = $this->pdo->select()->from('psb_value')->where('psb_id', '=', $form_id)->execute()->fetchAll();
            foreach ($psbData as &$value) {
                $value['data'] = json_decode($value['data'], true);
                $value['verification'] = json_decode($value['verification'], true);
            }
            return $psbData;
            unset($value);
        }else{
            $psbData = $this->pdo->select()->from('psb_value')->where('id', '=', $id)->execute()->fetch();
            $psbData['data'] = json_decode($psbData['data'], true);
            $psbData['verification'] = json_decode($psbData['verification'], true);
            return $psbData;
        }
    }

    public function findDataByFormNameUrl($url){
        $id = $this->pdo->select(['id'])->from('psb')->where('name_url', '=', $url)->execute()->fetchColumn();
        return $this->getData($id);
    }

    public function setData($data, $id=null){
        if($id == null){
            return $this->pdo->insert(array_keys($data))->into('psb_value')->values(array_values($data))->execute(true);
        }else{
            return $this->pdo->update($data)->table('psb_value')->where('id', '=', $id)->execute();
        }
    }
}
