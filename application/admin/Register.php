<?php
namespace Wijaya\WebApp;
class Register extends \Wijaya\WebApp {
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

    public function actionsRegisUser($req, $res, $args){
        $token = $_POST['token'];
        $token = base64_decode($token);
        $token = json_decode($token, true);
        if($token['email'] == $_POST['email'] && date("Y-m-d H:i:s", strtotime($token['valid'])) > date("Y-m-d H:i:s")){
            $data = [
                'username' => $_POST['username'],
                'password' => sha1($_POST['password']),
                'status' => json_encode([
                    'status' => 'active',
                    'token' => ''
                ]),
            ];
            $complete = $this->pdo->update($data)->table('users')->where('username', '=', $token['username'])->where('email', '=', $token['email']);
            if($complete->execute()){
                $this->flash->addMessage('RegisSuccess', true);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminAuthLoginHTML'));
            }
        }
    }

    public function ValidateUserName($req, $res, $args){
        if($this->pdo->select(['count(id)'])->from('users')->where('username', '=', $_GET['username'])->execute()->fetchColumn() < 1){
            return $res->withStatus(200);
        }else{
            return $res->withStatus(400, 'Username telah digunakan coba gunakan username yang lainnya');
        }
    }

    public function displayRegister($req, $res, $args){
        $count = $this->pdo->select(['count(id)'])->from('users')->whereLike('status', '%'.$args['token'].'%')->execute()->fetchColumn();
        $token = base64_decode($args['token']);
        $req = $req->withAttribute('token', json_decode($token, true));
        $req = $req->withAttribute('tokenRaw', $args['token']);
        $req = $req->withAttribute('valid', ($count != 0 ? true: false));
        return $this->view->render($res, 'admin/users/register.twig', $req->getAttributes());
    }

    public function __invoke($req, $res, $args){
        $method = $req->getMethod();
        switch ($method) {
            case 'GET':
                return $this->displayRegister($req, $res, $args);
                break;
            case 'POST':
                return $this->actionsRegisUser($req, $res, $args);
                break;
            default:
                return $res->withStatus(404);
                break;
        }
    }

}
