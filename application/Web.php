<?php
class Web{
    protected $db;
    protected $slug;
    protected $view;
    protected $mailer;
    protected $session;
    protected $flash;
    protected $manager;
    protected $diff;
    protected $filesize;
    protected $router;

    /**
     * Default Admin Class Constructor
     * @private
     * @param function $container Slim Framework Container Interface
     */
    function __construct($container) {
        $this->db = $container->get('db');
        $this->pdo = $container->get('pdo');
        $this->slug = $container->get('slug');
        $this->session = $container->get('session');
        $this->diff = $container->get('diff');
        $this->filesize = $container->get('filesize');
        $this->tags = $container->get('tags');
        $this->mailer = $container->get('mailer');
        $this->view = $container->get('view');
        $this->router = $container->get('router');
        $this->flash = $container->get('flash');
    }
    /**
     * Admin Page Middleware(Sessions)
     * @private
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  callable                                 $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($req, $res, $next){
        // $menu = $this->db->query("select meta from menus where group_id='1'")->fetchColumn();
        // $req = $req->withAttribute('main_menu', json_decode($menu));
        //
        // // web settings
        // $settings = [];
        // foreach($this->db->query("select * from settings")->fetchAll(PDO::FETCH_ASSOC) as $val){
        //     $settings[$val['type']] = [];
        //     foreach(json_decode($val['config']) as $key => $config){
        //         $settings[$val['type']][$key] = base64_decode($config);
        //     }
        // }
        // $req = $req->withAttribute('settings', $settings);
        // $res = $next($req, $res);
        // return $res;
    }

}
