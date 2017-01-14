<?php
namespace Wijaya\WebApp\View;
class PSB extends \Wijaya\WebApp {
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

    public function homePsb($req, $res, $args){
        $req = $req->withAttribute('PSB', $this->psbModel->getPSBform());
        return $this->view->render($res, 'admin/psb/index.twig', $req->getAttributes());
    }

    public function PSBData($req, $res, $args){
        $req = $req->withAttribute('PSBData', $this->psbModel->getPSBData($args['psb_id']));
        return $this->view->render($res, 'admin/psb/psb-data.twig', $req->getAttributes());
    }

    public function fieldPSB($req, $res, $args){
        $regnumber = (isset($args['regnumber']) ? $args['regnumber'] : null);
        $psbForm = $this->psbModel->getPSBForm($args['psb_id']);
        $req = $req->withAttribute('psb_form', $psbForm);
        $req = $req->withAttribute('step', isset($args['step']) ? $args['step'] : 1);
        $req = $req->withAttribute('regnumber', isset($args['regnumber']) ? $args['regnumber'] : 0);
        return $this->view->render($res, 'web/register/index.twig', $req->getAttributes());
    }
}
