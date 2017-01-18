<?php
namespace Wijaya\WebApp\Controller;
class WebPPDB extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $psbModel;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->psbModel = new \Wijaya\WebApp\Models\PSB($ci);
    }
    public function newData($req, $res, $args){
        $PPDBModels = new \Wijaya\WebApp\Models\PPDB($this->ci);
        $form = $PPDBModels->findFormByNameUrl($args['name']);
        if($form['type'] == 'reguler'){
            $newData = $this->newDataReguler($_POST, $form, $req->getUploadedFiles());
            if($newData){
                $this->flash->addMessage('PPDBData', $newData);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('web_PPDB_form', [
                    'name'=>$form['name_url']
                ]));
            }
        }
    }

    public function newDataReguler($meta, $form, $files){
        $PPDBModels = new \Wijaya\WebApp\Models\PPDB($this->ci);
        $filename = $this->slug->file(date('U').'-'.$files['ngrades']->getClientFilename());
        $files['ngrades']->moveTo('public/reg/'.$filename);
        $meta['ngrades']['image'] = $filename;
        $query = [
            'psb_id'=>$form['id'],
            'regnumber'=>date('U').substr($meta['bio']['nisn'], -4),
            'submited'=>date("Y-m-d H:i:s"),
            'updated'=>date("Y-m-d H:i:s"),
            'meta'=>json_encode($meta)
        ];
        $save = $PPDBModels->setData($query);
        if($save){
            $data = $PPDBModels->getData($save);
            return $data;
        }else{
            return false;
        }
    }
}
