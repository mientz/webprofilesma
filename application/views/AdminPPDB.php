<?php
namespace Wijaya\WebApp\View;
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

    public function form($req, $res, $args){
        $PPDBModels = new \Wijaya\WebApp\Models\PPDB($this->ci);
        $req = $req->withAttribute('forms', $PPDBModels->getForm());
        return $this->view->render($res, 'admin/ppdb/form.twig', $req->getAttributes());
    }

    public function data($req, $res, $args){
        $PPDBModels = new \Wijaya\WebApp\Models\PPDB($this->ci);
        $req = $req->withAttribute('form', $PPDBModels->findFormByNameUrl($args['name']));
        $req = $req->withAttribute('datas', $PPDBModels->findDataByFormNameUrl($args['name']));
        return $this->view->render($res, 'admin/ppdb/data.twig', $req->getAttributes());
    }
}
