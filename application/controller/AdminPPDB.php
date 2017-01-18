<?php
namespace Wijaya\WebApp\Controller;
class AdminPPDB extends \Wijaya\WebApp {
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

    public function newForm($req, $res, $args){
        $PPDBModels = new \Wijaya\WebApp\Models\PPDB($this->ci);
        $data = $_POST;
        $data['name_url'] = $this->slug->make($data['name'].date('U'));
        $data['start'] = date("Y-m-d", strtotime($data['start']));
        $data['end'] = date("Y-m-d", strtotime($data['end']));
        $data['messages'] = json_encode([
            'before'=>$data['before'],
            'after'=>$data['after'],
        ]);
        unset($data['before']);
        unset($data['after']);
        unset($data['files']);
        $form = $PPDBModels->setForm($data);
        if($form){
            $res = $res->withJson($this->report->success(__FUNCTION__, $PPDBModels->getForm($form)));
        }else{
            $res = $res->withJson($this->report->fail(__FUNCTION__, 'Formulir gagal Tersimpan'));
        }
        return $res;
    }

}
