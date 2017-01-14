<?php
namespace Wijaya\WebApp\Models;

class Settings extends \Wijaya\WebApp {
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

    public function getSettings(){
        $settings = [];
        foreach($this->pdo->select()->from('settings')->execute()->fetchAll() as $val){
            $settings[$val['type']] = [];
            foreach(json_decode($val['config']) as $key => $config){
                $settings[$val['type']][$key] = base64_decode($config);
            }
        }
        return $settings;
    }
}
