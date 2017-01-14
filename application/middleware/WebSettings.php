<?php
namespace Wijaya\WebApp\Middleware;
class WebSettings extends \Wijaya\WebApp{
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
        $settingsModel = new \Wijaya\WebApp\Models\Settings($this->ci);
        $req = $req->withAttribute('settings', $settingsModel->getSettings());
        $res = $next($req, $res);
        return $res;
    }
}
