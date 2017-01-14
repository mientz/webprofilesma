<?php
namespace Wijaya\WebApp\Controller;
class Users extends \Wijaya\WebApp {
    /**
     * Slim Framework container Interface
     * @var Slim\Container
     */
    protected $ci;
    protected $userModel;
    /**
     * Constructor
     * @private
     * @param Slim\Container $ci
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->userModel = new \Wijaya\WebApp\Models\Users($ci);
    }

    public function getUsers($req, $res, $args){
        $res = $res->withJson($this->userModel->getUserGroupsById(1));
        return $res;
    }
}
