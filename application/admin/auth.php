<?php
namespace Wijaya\WebApp;
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

    public function CheckUserAuth($username, $password){
        return $this->pdo
            ->select()
            ->from('users')
            ->where('username', '=', $username)
            ->where('password', '=', sha1($password))
            ->where('deleted', '=', 0)
            ->orWhere('email', '=', $username)
            ->groupBy('id')
            ->execute()->fetch();
    }

    public function displayLogin($req, $res, $args) {
        $req = $req->withAttribute('AuthError', $this->flash->getMessage('AuthError'));
        $req = $req->withAttribute('RegisSuccess', $this->flash->getMessage('RegisSuccess'));
        return $this->view->render($res, 'admin/users/login.twig', $req->getAttributes());
    }

    public function actionLogin($req, $res, $args) {
        $userData = $this->CheckUserAuth($_POST["username"], $_POST["password"]);
        if($userData){
            $this->session->set('user_id', $userData["id"]);
            $this->session->set('groupKey', 0);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminDashboard'));
        }else{
            $this->flash->addMessage('AuthError', true);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminLogin'));
        }
    }

    public function actionSwitchGroup($req, $res, $args){
        $headerData = $req->getParsedBody();
        $this->session->set('groupKey', $headerData['key']);
        return $res->withJson(['success'=>true, 'actions'=>'Switch Groups Session', 'value'=>$headerData['key'], 'return'=>null]);
    }

    public function actionLogout($req, $res, $args){
        $this->session->destroy();
        return $res->withJson(['success'=>true, 'actions'=>'Logout', 'value'=>null, 'return'=>null]);
    }

    public function __invoke($req, $res, $args){
        $method = $req->getMethod();
        switch($method){
            default:
            case 'GET':
                return $this->displayLogin($req, $res, $args);
                break;
            case 'POST':
                return $this->actionLogin($req, $res, $args);
                break;
            case 'PUT':
                return $this->actionSwitchGroup($req, $res, $args);
                break;
            case 'DELETE':
                return $this->actionLogout($req, $res, $args);
                break;
        }
    }
}
