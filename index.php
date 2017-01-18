<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$settings = require 'settings.php';
$app = new \Slim\App($settings);
// whoops custom error display
$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);
// session midleware settings
$app->add(new \Slim\Middleware\Session([
    'name' => 'SMA_N_3_BANGKALAN',
    'autorefresh' => true,
    'lifetime' => '1 days'
]));

$container = $app->getContainer();
require 'container.php';

// base url midleware
$app->add(new \Wijaya\WebApp\Middleware\Path($container));

$app->any('/register/{token}', \Wijaya\WebApp\Register::class)->setname('AdminRegister');
$app->group('/auth', function() {
    $this->get('-login.html', \Wijaya\WebApp\View\Auth::class.':Login')->setname('AdminLogin'); // Login Page
    $this->post('-login.html', \Wijaya\WebApp\Controller\Auth::class.':Login'); // Login Page
    $this->get('-logout.html', \Wijaya\WebApp\Controller\Auth::class.':Logout')->setname('AdminLogout'); // Login Page
});

$app->group('/admin', function() {
    $this->get('.html', \Wijaya\WebApp\Admin\Dashboard::class)->setname('AdminDashboard');
    $this->group('ppdb', function(){
        $this->get('forms.html', '\Wijaya\WebApp\View\AdminPPDB:form')->setname('admin_PPDB_forms');
        $this->get('data{name}.html', '\Wijaya\WebApp\View\AdminPPDB:data')->setname('admin_PPDB_data');
        $this->group('actions', function(){
            $this->post('newform', '\Wijaya\WebApp\Controller\AdminPPDB:newForm')->setname('admin_PPDB_forms_new');
        });
    });
    //
    // $this->group('/api', function(){
    //     $this->group('/ppdb', function(){
    //         $this->post('/forms/{psb_id}', '\Wijaya\WebApp\Controller\PSB:adminPPDBForms');
    //         $this->post('/data', '\Wijaya\WebApp\Controller\PSB:adminPPDBData');
    //     });
    //     // $this->any('/psb[/{params:.*}]', \Wijaya\WebApp\Controller\PSB::class)->setname('ApiPSB');
    //     $this->any('/psb[/{id:[0-9]+}]', \Wijaya\WebApp\Controller\PSB::class.':getPSBForm')->setname('ApiPSB_form');
    //     $this->any('/psb/data[/{id:[0-9]+}]', \Wijaya\WebApp\Controller\PSB::class.':getPSBForm')->setname('ApiPSB_form');
    //     $this->any('/psb/data/verification[/{regnumber:[0-9]+}]', \Wijaya\WebApp\Controller\PSB::class.':getPSBForm')->setname('ApiPSB_DataRegnumber');
    //     $this->group('/get', function(){
    //         $this->get('/post[/{id}]', \Wijaya\WebApp\Controller\Post::class)->setname('ApiPost');
    //         $this->get('/psb[/{psb_id}]', \Wijaya\WebApp\Controller\PSB::class.':getPSBData')->setname('ApiPSB');
    //     });
    //     $this->group('/new', function(){
    //         $this->post('/psb', \Wijaya\WebApp\Controller\PSB::class.':newPSBForm')->setname('ApiPSB_new');
    //     });
    //     $this->group('/edit', function(){
    //         $this->post('/psb[/{id}]', \Wijaya\WebApp\Controller\PSB::class.':editPSBForm')->setname('ApiPSB_edit');
    //         // $this->post('/psb/ver-[/{type}[/{regnumber}]]', \Wijaya\WebApp\Controller\PSB::class.':verification')->setname('ApiPSB_verification');
    //     });
    // });
        // // $this->map(['GET', 'POST'], '/ppdb[/{id}]', '\Wijaya\WebApp\Controller\PSB:adminPSBForm')->setname('admin_psb_form');
        // $this->get('/psb/form', \Wijaya\WebApp\View\PSB::class.':homePsb')->setname('AdminPSB');
        // $this->get('/psb/data/{psb_id}', \Wijaya\WebApp\View\PSB::class.':PSBData')->setname('AdminPSB_DataView');

})
->add(new \Wijaya\WebApp\Middleware\AuthData($container));

$app->group('/', function() {
    // $this->any('/ppdb/{name}')
    // $this->get('/ppdb[/{psb_id:[0-9]+}[/langkah-{step:[0-9]+}[/{regnumber:[0-9]+}]]]', \Wijaya\WebApp\View\PSB::class.':fieldPSB')->setname('WebPSB');
    $this->group('ppdb', function(){
        $this->get('-{name}.html', '\Wijaya\WebApp\View\WebPPDB:form')->setname('web_PPDB_form');
        $this->group('-actions', function(){
            $this->post('-newdata-{name}.html', '\Wijaya\WebApp\Controller\WebPPDB:newData')->setname('web_PPDB_data_new');
        });
    });

    $this->group('/api', function(){
        $this->post('/psb-data/{psb_id:[0-9]+}[/{step:[0-9]+}[/{regnumber:[0-9]+}]]', '\Wijaya\WebApp\Controller\PSB:newPSBData')->setname('ApiPSB_DataSave');
    });
})
->add(new \Wijaya\WebApp\Middleware\WebSettings($container));
// $app->group('/auth', function(){
//     $this->group('-login', function(){
//         $this->post('/lol', 'Auth:actionLogin')->setname('postAdminAuthLogin302'); // Login Action
//
//     });
//     // logout route
//     $this->get('-logout', 'Auth:actionLogout')->setname('getAdminAuthLogout302');
//     // switch group route
//     $this->get('-change/{key}', 'Auth:actionSwitchGroup')->setname('getAdminSwtichGroup302');
//     // user register
//     $this->get('-reg/{token}', 'Auth:displayRegister')->setname('getAdminNewRegHTML');
// });
// /*
//  * Route Group For Api => URL/Api
//  */
// $app->group('/apia', function () {
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/auth', function () {
//         // actions post changes
//         $this->get('-valid/username', '\Auth:actionsValidateUserName')->setName('postAdminAuthValidateUsername');
//         // actions post changes
//         $this->post('-doreg', '\Auth:actionsRegisUser')->setName('postAdminDoRegis302');
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/post', function () {
//         // actions post changes
//         $this->post('/change[/{id}[/{action}]]', '\Post:actionsChangePostStatus')->setName('postAdminPostChangeJSON')->add('Admin');
//         // action save post changes
//         $this->post('/save[/{id}]', '\Post:actionsSavePost')->setName('postAdminPostSaveJSON')->add('Admin');
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/postmedia', function () {
//         // actions post changes
//         $this->post('/upload', '\Postmedia:actionsUploadPostmedia')->setName('postAdminPostmediaUploadJSON')->add('Admin');
//         // actions post changes
//         $this->post('/delete[/{id}[/{file:.*}]]', '\Postmedia:actionsDeletePostmedia')->setName('getAdminPostmediaDeleteJSON')->add('Admin');
//         // actions post changes
//         $this->get('/list', '\Postmedia:actionsGetPostmedia')->setName('getAdminPostmediaListJSON')->add('Admin');
//     });
//     /*
//     * Route Group For Admin -> Category => URL/admin/category
//     */
//     $this->group('/category', function () {
//         // return category list json
//         $this->get('/list', '\Category:actionsGetCategory')->setName('getAdminCategoryListJSON')->add('Admin');
//         // insert new category actions
//         $this->post('/new', '\Category:actionsNewCategory')->setName('postAdminCategoryShorthandInsertJSON')->add('Admin');
//
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/users', function () {
//         // display userlist page
//         $this->post('', '\Users:actionsGetUserLists')->setName('getAdminUserListJSON')->add('Admin');
//         // display userlist page
//         $this->post('/new', '\Users:actionsNewGroups')->setName('setAdminUserListJSON')->add('Admin');
//         // display userlist page
//         $this->post('/invite', '\Users:actionsInviteUser')->setName('setAdminInviteusersJSON')->add('Admin');
//         // display userlist page
//         $this->post('/remove[/{id}]', '\Users:actionsRemoveUsers')->setName('setAdminremoveUsersJSON')->add('Admin');
//
//     });
//     $this->group('/groups', function () {
//         // display userlist page
//         $this->post('/new', '\Groups:actionsNewGroups')->setName('postAdminNewGroupsJSON')->add('Admin');
//         // display userlist page
//         $this->post('/delete[/{id}]', '\Groups:actionsDeleteGroups')->setName('postAdminDeleteGroupsJSON')->add('Admin');
//         // display userlist page
//         $this->post('/simple-edit[/{id}]', '\Groups:actionsSimpleEditGroup')->setName('postAdminSimpleEditGroupsJSON')->add('Admin');
//         // display userlist page
//         $this->post('/kick-user-group[/{id}]', '\Groups:actionsKickUserGroupRole')->setName('postAdminKickUserGroupsJSON')->add('Admin');
//         // display userlist page
//         $this->post('/add-user-group', '\Groups:actionsAddUserGroupRole')->setName('postAdminAddUserGroupsJSON')->add('Admin');
//         // display userlist page
//         $this->post('/change-user-groups[/{id}]', '\Groups:actionsChangeUserGroupRole')->setName('postAdminChangeUserGroupsJSON')->add('Admin');
//
//     });
//
//     $this->group('/settings', function() {
//         $this->post('[/{type}]', '\Settings:ActionsSaveSettings')->setName('postAdminSettingsJSON');
//     });
//
//     $this->group('/psb', function () {
//         $this->post('/new', '\PSB:actionsNewPSB')->setName('getAdminNewPSBJSON');
//
//     });
// });
// /*
//  * Route Group For Admin => URL/admin
//  */
// $app->group('/admin', function () {
//     // route for dashboard
//     $this->get('', 'Dashboard:displayDashboard')->setName('getAdminDashboardHTML');
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/post', function () {
//         // display post list page
//         $this->get('/list[/{type}]', '\Post:displayPostsList')->setName('getAdminPostListHTML');
//         // display add post page
//         $this->get('/new', '\Post:displayNewPost')->setName('getAdminPostAddHTML');
//         // display edit post page by id
//         $this->get('/edit[/{id}]', '\Post:displayEditPost')->setName('getAdminPostEditHTML');
//         // display post vs revisions different page by id
//         $this->get('/diff[/{id}]', '\Post:displayDiffRevisionsPost')->setName('getAdminPostDiffHTML');
//         // display edit post page by id
//         $this->get('/revert[/{id}[/{rev_id}]]', '\Post:actionsRevertDiff')->setName('getAdminPostRevertRevisions302');
//
//     })->add('Post');
//     /*
//     * Route Group For Postmedia -> Category => URL/admin/postmedia
//     */
//     $this->group('/postmedia', function () {
//         // display postmedia list page
//         $this->get('', '\Postmedia:displayGetPostmedia')->setName('getAdminPostmediaHTML');
//         // display post list page
// //        $this->get('/list', '\Category:displayCategoryLists')->setName('getAdminCategoryListHTML');
//
//     });
//     /*
//     * Route Group For Admin -> Category => URL/admin/category
//     */
//     $this->group('/category', function () {
//         // display post list page
//         $this->get('/list', '\Category:displayCategoryLists')->setName('getAdminCategoryListHTML');
//         // display post list page
// //        $this->get('/list', '\Category:displayCategoryLists')->setName('getAdminCategoryListHTML');
//
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/users', function () {
//         // display userlist page
//         $this->get('', '\Users:getUserLists')->setName('getAdminUserListHTML');
//
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/groups', function () {
//         // display userlist page
//         $this->get('', '\Groups:displayGroupList')->setName('getAdminGroupsListHTML');
//         // display userlist page
//         $this->get('/edit[/{id}]', '\Groups:displayEditGroup')->setName('getAdminGroupsEditHTML');
//
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/settings', function () {
//         // display userlist page
//         $this->get('', '\Settings:displaySettings')->setName('getAdminSettingsHTML');
//
//     });
//     /*
//     * Route Group For Admin -> Users => URL/admin/users
//     */
//     $this->group('/psb', function () {
//         // display userlist page
//         $this->get('', '\PSB:displayPSBList')->setName('getAdminPSBListHTML');
//
//     });
//
// })->add('Admin');
//
// $app->group('', function(){
//     $this->group('/pendaftaran', function(){
//         $this->get('/reguler/{step}/{id}[/{data_id}]', '\Register:displayRegister')->setName('webRegisterReguler');
//         $this->post('/reguler/{step}/{id}[/{data_id}]', '\Register:actionsRegister')->setName('actionsRegisterPre');
//     });
// })->add('Web');
//

$app->any('/test', function($req, $res, $args){
    // $POST = new \Wijaya\WebApp\Admin\Post($container);
    if($req->isPost()){
        $files = $req->getUploadedFiles();
        $lala = [];
        foreach ($files as $input) {
            foreach ($input as $file){
                array_push($lala, $file->getClientFilename());
            }
        }
        return $res->withJson($lala);
    }else{
        return $this->view->render($res, 'coba.twig', $req->getAttributes());
    }
    // return $res->withJson(explode('/', $args['actions']));
});

$app->any('/us', \Wijaya\WebApp\Controller\Users::class.':getUsers');
// run the fucking app
$app->run();
