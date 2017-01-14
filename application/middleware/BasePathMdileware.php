<?php
namespace Wijaya\WebApp\Middleware;
class Path{
    public function __invoke($req, $res, $next) {
        $uri = $req->getUri();
        $req = $req->withAttribute('base_path', $uri->getBasePath());
        $req = $req->withAttribute('current_path', $uri->getPath());
        $res = $next($req, $res);
        return $res;
    }
}
