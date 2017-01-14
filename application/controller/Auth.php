<?php
namespace Wijaya\WebApp\Controller;
class Auth extends \Wijaya\WebApp {
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

    public function Login($req, $res, $args){
        $user = $this->userModel->getUserByUsernameAndPassword($_POST['username'], $_POST['password']);
        if($user){
            $this->session->set('user_id', $user["id"]);
            $this->session->set('groupKey', 0);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminDashboard'));
        }else{
            $this->flash->addMessage('AuthError', true);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminLogin'));
        }
    }

    public function Logout($req, $res, $args){
        $this->session->destroy();
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminLogin'));
    }
}
