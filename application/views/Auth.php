<?php
namespace Wijaya\WebApp\View;
class Auth extends \Wijaya\WebApp {
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

    public function Login($req, $res, $args){
        $req = $req->withAttribute('AuthError', $this->flash->getMessage('AuthError'));
        $req = $req->withAttribute('RegisSuccess', $this->flash->getMessage('RegisSuccess'));
        return $this->view->render($res, 'admin/users/login.twig', $req->getAttributes());
    }
}
