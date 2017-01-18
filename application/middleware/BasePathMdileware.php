<?php
namespace Wijaya\WebApp\Middleware;

class Path extends \Wijaya\WebApp{
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
    public function __invoke($req, $res, $next) {
        $uri = $req->getUri();
        $route = $req->getAttribute('route');
        $req = $req->withAttribute('base_path', $uri->getBasePath());
        $req = $req->withAttribute('current_path', $uri->getPath());
        $this->view->addExtension(new \Wijaya\WebApp\Helper\Twig\ActiveMark(
            $route,
            $this->router
        ));
        $res = $next($req, $res);
        return $res;
    }
}
