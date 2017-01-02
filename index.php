<?php
date_default_timezone_set('Asia/Jakarta');
setlocale(LC_TIME, 'id_ID');
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$settings = require 'settings.php';
$app = new \Slim\App($settings);

$container = $app->getContainer();
// whoops custom error display
$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

// session midleware settings
$app->add(new \Slim\Middleware\Session([
    'name' => 'SMA_N_3_BANGKALAN',
    'autorefresh' => true,
    'lifetime' => '1 days'
]));
// base url midleware
$app->add(function ($req, $res, $next) {
    $uri = $req->getUri()->getBaseUrl();
    $req = $req->withAttribute('uri_base', $uri);
    $res = $next($req, $res);
    return $res;
});
// autoload class
$require_dir = [
    "application",
    "application/helper",
    "application/admin",
    "application/web",
];

foreach ($require_dir as $dir){
    foreach (glob($dir."/*.php") as $filename){
        require $filename;
    }
}

/*
 * Route Group For Autheticate the user
 */
$app->group('/auth', function(){
   /*
    * Route Group For Login
    */
    $this->group('-login', function(){
        // login page route
        $this->get('', 'Auth:displayLogin')->setname('getAdminAuthLoginHTML');
        // login action route
        $this->post('', 'Auth:actionLogin')->setname('postAdminAuthLogin302');
    });
    // logout route
    $this->get('-logout', 'Auth:actionLogout')->setname('getAdminAuthLogout302');
    // switch group route
    $this->get('-change/{key}', 'Auth:actionSwitchGroup')->setname('getAdminSwtichGroup302');
});
/*
 * Route Group For Api => URL/Api
 */
$app->group('/api', function () {
    /*
    * Route Group For Admin -> Users => URL/admin/users
    */
    $this->group('/post', function () {
        // actions post changes
        $this->post('/change[/{id}[/{action}]]', '\Post:actionsChangePostStatus')->setName('postAdminPostChangeJSON')->add('Admin');
        // action save post changes
        $this->post('/save[/{id}]', '\Post:actionsSavePost')->setName('postAdminPostSaveJSON')->add('Admin');
    });
    /*
    * Route Group For Admin -> Category => URL/admin/category
    */
    $this->group('/category', function () {
        // return category list json
        $this->get('/list', '\Category:actionsGetCategory')->setName('getAdminCategoryListJSON')->add('Admin');
        // insert new category actions
        $this->post('/new', '\Category:actionsNewCategory')->setName('postAdminCategoryShorthandInsertJSON')->add('Admin');

    });
});
/*
 * Route Group For Admin => URL/admin
 */
$app->group('/admin', function () {
    // route for dashboard
    $this->get('', 'Dashboard:displayDashboard')->setName('getAdminDashboardHTML');
    /*
    * Route Group For Admin -> Users => URL/admin/users
    */
    $this->group('/post', function () {
        // display post list page
        $this->get('/list[/{type}]', '\Post:displayPostsList')->setName('getAdminPostListHTML');
        // display add post page
        $this->get('/new', '\Post:displayNewPost')->setName('getAdminPostAddHTML');
        // display edit post page by id
        $this->get('/edit[/{id}]', '\Post:displayEditPost')->setName('getAdminPostEditHTML');
        // display post vs revisions different page by id
        $this->get('/diff[/{id}]', '\Post:displayDiffRevisionsPost')->setName('getAdminPostDiffHTML');
        // display edit post page by id
        $this->get('/revert[/{id}[/{rev_id}]]', '\Post:actionsRevertDiff')->setName('getAdminPostRevertRevisions302');

    })->add('Post');
    /*
    * Route Group For Admin -> Category => URL/admin/category
    */
    $this->group('/category', function () {
        // display post list page
        $this->get('/list', '\Category:displayCategoryLists')->setName('getAdminCategoryListHTML');
        // display post list page
//        $this->get('/list', '\Category:displayCategoryLists')->setName('getAdminCategoryListHTML');

    });
    /*
    * Route Group For Admin -> Users => URL/admin/users
    */
    $this->group('/users', function () {
        // display userlist page
        $this->get('', '\Users:getUserLists')->setName('getAdminUserListHTML');

    });

})->add('Admin');

// run the fucking app
$app->run();
