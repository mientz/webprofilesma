<?php
namespace Wijaya\WebApp\Helper\Twig;
class ActiveMark extends \Twig_Extension
{
    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    private $route;
    private $router;

    public function __construct($route, $router)
    {
        $this->route = $route;
        $this->router = $router;
    }

    public function getName()
    {
        return 'Path Active Mark';
    }

    public function getGlobals()
    {
        return array(
            'route_name' => $this->route->getName(),
        );
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('active', array($this, 'checkRoute')),
            new \Twig_SimpleFunction('hidden_path', array($this, 'dynamicRoute'))
        ];
    }

    public function checkRoute($name = null, $hidden=false)
    {
        if(is_array($name)){
            if(in_array($this->route->getName(), $name)){
                return 'active';
            }
        }else{
            if($this->route->getName() == $name){
                return 'active';
            }else{
                if($hidden){
                    return 'hidden';
                }
            }
        }
    }

    public function dynamicRoute($name)
    {
        if($this->route->getName() == $name){
            return $this->router->pathFor($name, $this->route->getArguments());
        }
    }
}
