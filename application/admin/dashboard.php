<?php
class Dashboard extends Admin{
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
     * Dashboard page
     * @private
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return \Psr\Http\Message\ResponseInterface      HTML Dasboard Page
     */
    public function displayDashboard($req, $res, $args){
        return $this->view->render($res, 'admin/dashboard/index.html', $req->getAttributes());
    }
}
