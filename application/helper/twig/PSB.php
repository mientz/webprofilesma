<?php
namespace Wijaya\WebApp\Helper\Twig;
class PSB extends \Twig_Extension
{
    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    private $ci;

    public function __construct($ci)
    {
        $this->ci = $ci;
    }

    public function getName()
    {
        return 'PSBData';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('countPSBdata', array($this, 'getPSBData'))
        ];
    }

    public function getPSBData($psb_id)
    {
        $psbModel = new \Wijaya\WebApp\Models\PSB($this->ci);
        return $psbModel->getPSBDataCount($psb_id);
    }
}
