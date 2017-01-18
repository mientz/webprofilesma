<?php
namespace Wijaya\WebApp\View;
class WebPPDB extends \Wijaya\WebApp {
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
        $form = $PPDBModels->findFormByNameUrl($args['name']);
        $req = $req->withAttribute('form', $form);
        $req = $req->withAttribute('PPDBData', $messages = $this->flash->getMessage('PPDBData'));
        if($form['type'] == 'reguler'){
            return $this->view->render($res, 'web/ppdb/reguler.twig', $req->getAttributes());
        }else{

        }
    }
}
