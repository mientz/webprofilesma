<?php
class Auth extends Admin {
    /*
     * This Class Only Variable
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
    }
    /**
     * Check User Credentials
     * @param  string $username [[Description]]
     * @param  string $password [[Description]]
     * @return object query result
     */
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
    /**
     * Mdileware To Redirect if user session in still exist
     * @private
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  callable                                 $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($req, $res, $next) {

        if(isset($this->session->user_id)){

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminDashboardHTML'));

        }

        $res = $next($req, $res);

        return $res;
    }
    /**
     * User Auth Credential Check
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return \Psr\Http\Message\ResponseInterface      Redirect(302)
     */
    public function actionLogin($req, $res, $args) {

        $userData = $this->CheckUserAuth($_POST["username"], $_POST["password"]);
        echo json_encode($userData);

        if($userData){

            $this->session->set('user_id', $userData["id"]);
            $this->session->set('groupKey', 0);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminDashboardHTML'));

        }else{

            $this->flash->addMessage('AuthError', true);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminAuthLoginHTML'));

        }

    }
    /**
     * Switch Group for editing
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return \Psr\Http\Message\ResponseInterface      Redirect(302)
     */
    public function actionSwitchGroup($req, $res, $args){

        $this->session->set('groupKey', $args['key']);
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminDashboardHTML'));

    }
    /**
     * Logout and destroy all session
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return \Psr\Http\Message\ResponseInterface      Redirect(302)
     */
    public function actionLogout($req, $res, $args){

        $this->session->destroy();
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminAuthLoginHTML'));

    }
    /**
     * Display Login Page
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return \Psr\Http\Message\ResponseInterface      HTML
     */
    public function displayLogin($req, $res, $args){

        return $this->view->render($res, 'admin/users/login.html', [

            'AuthError' => $this->flash->getMessage('AuthError')

        ]);
    }

}

//$app->group('/auth', function () {
//    /**
//     * [[Login Page]]
//     */
//    $this->get('/login[/{error}]', function ($req, $res, $args) {
//        return $this->view->render($res, 'admin/login.html', [
//            'error' => (array_key_exists('error', $args) ? true : false),
//            'flash' => $this->flash->getMessages()
//        ]);
//    })->setName('pre-login');
//
//    /*
//     * [[Login Process]]
//     */
//    $this->post('/login', function ($req, $res, $args) {
//        $select = $this->db->prepare("select * from users where password=:password and (username=:username or email=:email) and deleted = '0' group by id");
//        $select->bindParam(':username', $_POST["username"], PDO::PARAM_STR);
//        $select->bindParam(':email', $_POST["username"], PDO::PARAM_STR);
//        $select->bindParam(':password', sha1($_POST["password"]), PDO::PARAM_STR);
//        $select->execute();
//        $count = $select->rowCount();
//        $data = $select->fetch(PDO::FETCH_ASSOC);
//        if($count == 1){
//            $this->session->set('user_id', $data["id"]);
//            $this->session->set('group_id', null);
//            $this->session->set('groupKey', 0);
//            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-dashboard'));
//        }else{
//            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login', ['error'=>'error']));
//        }
//        return $res;
//    })->setName('login');
//
//})->add(function ($req, $res, $next) {
//    if(isset($this->session->user_id)){
//        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-dashboard'));
//    }
//    $res = $next($req, $res);
//    return $res;
//    return $res;
//});
//
//
//$app->get('/change_group/{id}', function ($req, $res, $args) {
//    $this->session->set('group_id', $args['id']);
//    $last_page = $this->flash->getMessages();
//    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-dashboard'));
//})->setName('change-group');
//
///*
// * [[logout]]
// */
//$app->get('/logout', function ($req, $res, $args) {
//    $this->session->destroy();
//    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('pre-login'));
//})->setName('logout');
