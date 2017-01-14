<?php
namespace Wijaya\WebApp\Middleware;
class AuthData extends \Wijaya\WebApp{
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
        if(!isset($this->session->user_id)){
            if($req->isGet()) {
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('AdminLogin'));
            }else {
                return $res->withStatus(405)->withJson(['success'=>false, 'cause'=>'Session Expired']);
            }
        }else{
            $route = $req->getAttribute('route');
            if(isset($_GET['change_group'])){
                $this->session->set('groupKey', $_GET['change_group']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor($route->getName(), $route->getArguments()));
            }
            $authData = [
                'user' => $this->userModel->getUserGroupsById($this->session->user_id),
                'groupKey' => $this->session->groupKey,
                'user_id' => $this->session->user_id,
            ];
            $req = $req->withAttribute('AuthData', $authData);
            $res = $next($req, $res);
            return $res;
        }
    }
}
