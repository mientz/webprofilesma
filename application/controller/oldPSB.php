<?php
namespace Wijaya\WebApp\Controller;
class oldPSB extends \Wijaya\WebApp {
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

    public function webPSBData($req, $res, $args){
        if($args['regnumber'] == 0){
            return $this->newPSBData($req, $res, $args, $_POST);
        }else{
            return $this->updatePSBData($req, $res, $args, $_POST, $req->getUploadedFiles());
        }
    }

    public function newPSBData($req, $res, $args, $postData){
        $data = [
            'psb_id'=>$args['psb_id'],
            'regnumber'=>date('U').substr($postData['bio']['nisn'], -4),
            'submited'=>date("Y-m-d H:i:s"),
            'updated'=>date("Y-m-d H:i:s"),
            'data'=>json_encode($postData)
        ];
        $save = $this->psbModel->setPSBData($data);
        if($save){
            $regnumber = $this->psbModel->getPSBData($save);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                'psb_id'=>$args['psb_id'],
                'regnumber'=>$regnumber,
                'step'=>3
            ]));
        }
    }

    public function updatePSBData($req, $res, $args, $postData, $files){
        $prevData = $this->psbModel->getPSBDataByRegNumber($args['psb_id'], $args['regnumber']);
        $psbData = array_merge($prevData['data'], $postData);
        foreach ($files as $inputKey => $input) {
            foreach ($input as $filekey => $file) {
                if($file['image']->getClientFilename() != ''){
                    $filename = time().'-'.$this->slug->file($file['image']->getClientFilename());
                    $file->moveTo('public/reg');
                    $psbData[$inputKey][$filekey]['image'] =  $filename;
                }
            }
        }
        $save = $this->psbModel->setPSBData([
            'updated'=>date("Y-m-d H:i:s"),
            'data'=>json_encode($psbData)
        ], $args['regnumber']);
        if($save){
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                'psb_id'=>$args['psb_id'],
                'regnumber'=>$args['regnumber'],
                'step'=>$args['step']+1
            ]));
        }
    }

    public function adminNewPPDB($req, $res, $args){
        $data = $_POST;
        $data['name_url'] = $this->slug->make($data['name'].time()));
        $data['start'] = date("Y-m-d", strtotime($data['start']));
        $data['end'] = date("Y-m-d", strtotime($data['end']));
        $psbRes = $this->psbModel->setPSBForm($data);
        if($psbRes){
            $res = $res->withJson($this->report->success(__FUNCTION__, $this->psbModel->getPSBForm($psbRes)));
        }else{
            $res = $res->withJson($this->report->fail(__FUNCTION__, 'Formulir gagal Tersimpan'));
        }
        return $res;
    }

    public function adminUpdatePPDB($req, $res, $args){
        $data = $_POST;
        $data['name_url'] = $this->slug->make($data['name'].date("Y-m-d", strtotime($data['start'])));
        $data['start'] = date("Y-m-d", strtotime($data['start']));
        $data['end'] = date("Y-m-d", strtotime($data['end']));
        $data['messages'] = json_encode(array_merge($data['before'], $data['after']));
        $psbRes = $this->psbModel->setPSBForm($data, $args['id']);
        if($psbRes){
            $res = $res->withJson($this->report->success(__FUNCTION__, $this->psbModel->getPSBForm($psbRes)));
        }else{
            $res = $res->withJson($this->report->fail(__FUNCTION__, 'Formulir gagal Tersimpan'));
        }
        return $res;
    }

    public function newPSBForm($req, $res, $args){
        $data = $_POST;
        $data['name_url'] = $this->slug->make($data['name'].date("Y-m-d", strtotime($data['start'])));
        $data['start'] = date("Y-m-d", strtotime($data['start']));
        $data['end'] = date("Y-m-d", strtotime($data['end']));
        $psbRes = $this->psbModel->setPSBForm($data);
        if($psbRes){
            $res = $res->withJson($this->report->success(__FUNCTION__, $this->psbModel->getPSBForm($psbRes)));
        }else{
            $res = $res->withJson($this->report->fail(__FUNCTION__, 'Formulir gagal Tersimpan'));
        }
        return $res;
    }

    public function editPSBForm($req, $res, $args){
        $data = $_POST;
        $data['name_url'] = $this->slug->make($data['name'].date("Y-m-d", strtotime($data['start'])));
        $data['start'] = date("Y-m-d", strtotime($data['start']));
        $data['end'] = date("Y-m-d", strtotime($data['end']));
        $data['messages'] = json_encode(array_merge($data['before'], $data['after']));
        $psbRes = $this->psbModel->setPSBForm($data, $args['id']);
        if($psbRes){
            $res = $res->withJson($this->report->success(__FUNCTION__, $this->psbModel->getPSBForm($psbRes)));
        }else{
            $res = $res->withJson($this->report->fail(__FUNCTION__, 'Formulir gagal Tersimpan'));
        }
        return $res;
    }

    public function savePSBData($req, $res, $args){
        if($args['regnumber'] == 0){
            $data = [
                'psb_id'=>$args['psb_id'],
                'regnumber'=>date('U').substr($_POST['bio']['nisn'], -4),
                'submited'=>date("Y-m-d H:i:s"),
                'updated'=>date("Y-m-d H:i:s"),
                'data'=>json_encode($_POST)
            ];
            $save = $this->psbModel->setPSBData($data);
            if($save){
                $regnumber = $this->psbModel->getPSBData($save);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                    'psb_id'=>$args['psb_id'],
                    'regnumber'=>$regnumber,
                    'step'=>3
                ]));
            }else{
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                    'psb_id'=>$args['psb_id'],
                    'step'=>$args['step']
                ]));
            }
        }else{
            $prevData = $this->psbModel->getPSBDataByRegNumber($args['psb_id'], $args['regnumber'])['data'];
            $rawPost = array_merge($prevData, $_POST);
            $files = $req->getUploadedFiles();
            foreach ($files as $inputKey => $input) {
                foreach ($input as $filekey => $file) {
                    if($file['image']->getClientFilename() != ''){
                        $filename = time().'-'.$this->slug->file($file['image']->getClientFilename());
                        $file->moveTo('public/reg');
                        $rawPost[$inputKey][$filekey]['image'] =  $filename;
                    }
                }
            }
            $data = [
                'updated'=>date("Y-m-d H:i:s"),
                'data'=>json_encode($rawPost)
            ];
            $save = $this->psbModel->setPSBData($data, $args['regnumber']);
            if($save){
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                    'psb_id'=>$args['psb_id'],
                    'regnumber'=>$args['regnumber'],
                    'step'=>$args['step']+1
                ]));
            }else{
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('WebPSB', [
                    'psb_id'=>$args['psb_id'],
                    'regnumber'=>$args['regnumber'],
                    'step'=>$args['step']
                ]));
            }
            return $res->withJson($data);
        }
    }

    public function verification($req, $res, $args){
        $preVer = $this->psbModel->getPSBData($args['psb_id'], $args['id'])['verification'];
        if($args['type'] == 'grade'){
            $preVer[$_POST['what']][$_POST['smt']][$_POST['type']] = true;
        }elseif($args['type'] == 'cert'){

        }
        $data = [
            'verivication'=>$preVer
        ];
        return $this->psbModel->setPSBData($data, $args['regnumber']);
    }
}
