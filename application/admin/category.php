<?php

class Category extends WebApp{
    /*
     * This Class Only Variable
     */
    protected $ci;
    protected $user_id;
    protected $group_id;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci){
        $this->ci = $ci;
        parent::__construct($ci);
        $this->user_id = $this->session->user_id;
        $this->group_id = $this->getUserGroupData($this->user_id, $this->session->groupKey)['id'];
    }
    /**
     * Get Daefault ID Category 'Tidak Berkategori'
     * @return integer ID For Category 'Tidak Berkategori'
     */
    public function getDefaultCategoryId(){
        $groups = $this->getUserGroupData($this->user_id, $this->session->groupKey);
        return $this->pdo->select(['id'])->from('post_category')->where('name', '=', 'Tidak Berkategori')->where('group_id', '=', $this->group_id)->execute()->fetchColumn();
    }
    /**
     * Get Category List
     * @param  boolean [$excludeDefault = true] if true exclude the default 'Tidak Berkategori'
     * @return object  category details(+post count from this category)
     */
    public function getCategoryLists($excludeDefault = true){
        $post_categories = $this->pdo->select()->from('post_category')->where('group_id', '=', $this->group_id)->where('deleted', '=', 0);
        if($excludeDefault){
            $post_categories->where('id', '<>', $this->getDefaultCategoryId());
        }
        $post_categories = $post_categories->execute()->fetchAll();
        foreach($post_categories as $key => $category){
            $post_categories[$key]["post_count"] = $this->pdo->select(['count(id)'])
                ->from('post')
                ->where('category_id', '=', $category['id'])
                ->whereIn('status', [1, 3])
                ->execute()->fetchColumn();
        }
        return $post_categories;
    }
    /**
     * Get Category Detail By ID
     * @param  integer $id category id
     * @return object Category Details by id
     */
    public function getCategoryById($id){
        return $this->pdo->select()->from('post_category')->where('id', '=', $id)->execute()->fetch();
    }
    /**
     * insert new category
     * @param  object   $data value objcet
     * @return boolean true if insert success
     */
    public function insertCategory($data){
        $data['group_id'] = $this->group_id;
        return $this->pdo->insert(array_keys($data))->into('post_category')->values(array_values($data))->execute();
    }
    /**
     * update selected category
     * @param  integer $id   category selector by id
     * @param  object $data update value objcet
     * @return boolean true if success
     */
    public function updateCategory($id, $data){
        $update = $this->pdo->update($data)->table('post_category')->where('id', '=', $id);
        return $insert;
    }
    /**
     * Display Category List
     * @param  function $req  Slim Request Object
     * @param  function $res  Slim Respose object
     * @param  object $args URL Parameter Object
     * @return HTML HTML Rendered Page
     */
    public function displayCategoryLists($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'category']);
        $req = $req->withAttribute('page_category', $this->getCategoryLists());
        return $this->view->render($res, 'admin/category.html', $req->getAttributes());
    }
    /**
     * Display Category List + Selected Category To Edit
     * @param  function $req  Slim Request Object
     * @param  function $res  Slim Respose object
     * @param  object $args URL Parameter Object
     * @return HTML HTML Rendered Page
     */
    public function displaySelectedCategory($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'category']);
        $req = $req->withAttribute('page_category', $this->getCategoryLists());
        $req = $req->withAttribute('selected_category', $post_category);
        $req = $req->withAttribute('is_edit', $args['id']);
        return $this->view->render($res, 'admin/category.html', $req->getAttributes());

    }
    public function actionDeleteCategory($req, $res, $args){
        $deleted = $this->updateCategory($args['id'], [
            'deleted' => 1,
            'name_url' => ''
        ]);
        if($deleted){
            $update = $this->pdo
                ->update([
                    'category_id' => $this->getDefaultCategoryId($group_id)
                ])
                ->table('post')
                ->where('category_id', '=', $args['id'])
                ->execute();

            $res = $res->withJson([
                'success'=>true
            ]);
        }
    }
    public function actionsGetCategory($req, $res, $args){
        $res = $res->withJson($this->getCategoryLists(false));
        return $res;
    }
    public function actionsNewCategory($req, $res, $args){
        $data = [
            'group_id'  => $this->group_id,
            'name'      => $_POST['name'],
            'name_url'  => $this->slug->make($_POST['name'])
        ];
        if($this->insertCategory($data)){
            $res = $res->withJson([ 'inserted'=>true ]);
        }else{
            $res = $res->withJson([ 'inserted'=>true ]);
        }
        return $res;
    }

}

$app->group('/admin/category', function () {
    /*
     * JSON Response Group
     */
    $this->group('/json', function () {


//        $this->post('/insert', function ($req, $res, $args) {
//            /*
//            * list all category from current group data
//            */
//            $group_data = $req->getAttribute('current_group_data');
//            $name = $_POST['name'];
//            $name_url = preg_replace('/\s+/', '-', strtolower($name));;
//            $insert = $this->db->prepare("insert into post_category(group_id, name, name_url, deleted) values(:group_id, :name, :name_url, 0)");
//            $insert->bindParam(':group_id', $group_data['id'], PDO::PARAM_INT);
//            $insert->bindParam(':name', $name, PDO::PARAM_STR);
//            $insert->bindParam(':name_url', $name_url, PDO::PARAM_STR);
//            if($insert->execute()){
//                $res = $res->withJson([
//                    'inserted'=>true
//                ]);
//            }else{
//                $res = $res->withJson([
//                    'inserted'=>false
//                ]);
//            }
//            return $res;
//
//        })->setName('postAdminCategoryShorthandInsertJSON');

    });

    /*
     * HTML Response Group
     */

    $this->group('/html', function(){

        $this->get('/list[/{id}]', function ($req, $res, $args) {

            $group_id = $req->getAttribute('current_group_data')['id'];
            $req = $req->withAttribute('sidemenu', ['post'=>'category']);

            $default_id = $this->pdo->select(['id'])
                ->from('post_category')
                ->where('name', '=', 'Tidak Berkategori')
                ->where('group_id', '=', $group_id)
                ->execute()->fetchColumn();

            $post_categories = $this->pdo->select()
                ->from('post_category')
                ->where('group_id', '=', $group_id)
                ->where('id', '<>', $default_id)
                ->where('deleted', '=', 0)
                ->execute()->fetchAll();

            foreach($post_categories as $key => $category){
                $post_categories[$key]["post_count"] = $this->pdo->select(['count(id)'])
                    ->from('post')
                    ->where('category_id', '=', $category['id'])
                    ->whereIn('status', [1, 3])
                    ->execute()->fetchColumn();
            }

            $req = $req->withAttribute('page_category', $post_categories);

            if(isset($args['id'])){

                $post_category = $this->pdo->select()
                    ->from('post_category')
                    ->where('id', '=', $args['id'])
                    ->execute()->fetch();

                $req = $req->withAttribute('selected_category', $post_category);

                $req = $req->withAttribute('is_edit', $args['id']);

            }

            return $this->view->render($res, 'admin/category.html', $req->getAttributes());
        })->setName('admin-allcategory');

        $this->post('/edit[/{id}]', function ($req, $res, $args) { // edit or add category function

            $group_id = $req->getAttribute('current_group_data')['id'];

            if(isset($args['id'])){ // edit the data when $args['id'] is exist

                $update = $this->pdo
                    ->update([
                        'name' => $_POST['name'],
                        'name_url' => $this->slug->make($_POST['name']),
                        'description' => $_POST['description']
                    ])
                    ->table('post_category')
                    ->where('id', '=', $args['id']);

                if($update->execute()){

                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-allcategory'));

                }

            }else{ // insert the data otherwise

                $insert = $this->pdo
                    ->insert([
                        'group_id',
                        'name',
                        'name_url',
                        'description',
                        'deleted'
                    ])
                    ->into('post_category')
                    ->values([
                        $group_id,
                        $_POST['name'],
                        $this->slug->make($_POST['name']),
                        $_POST['description'], 0
                    ]);

                if($insert->execute()){

                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin-allcategory'));

                }
            }
        })->setName('postAdminCategoryAddOrEdit302');

        $this->post('/delete/{id}', function ($req, $res, $args) { // delete category function

            $group_id = $req->getAttribute('current_group_data')['id'];

            $default_id = $this->pdo->select(['id'])
                ->from('post_category')
                ->where('name', '=', 'Tidak Berkategori')
                ->where('group_id', '=', $group_id)
                ->execute()->fetchColumn();

            if(isset($args['id'])){

                $delete = $this->pdo
                    ->update([
                        'deleted' => 1,
                        'name_url' => ''
                    ])
                    ->table('post_category')
                    ->where('id', '=', $args['id'])
                    ->execute();

                if($delete){

                    $update = $this->pdo
                        ->update([
                            'category_id' => $default_id
                        ])
                        ->table('post')
                        ->where('category_id', '=', $args['id'])
                        ->execute();

                    $res = $res->withJson([
                        'success'=>true
                    ]);
                }

            }

            return $res;
        })->setName('admin-allcategory-delete');

    });

})->add('Admin');
