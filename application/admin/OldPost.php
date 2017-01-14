<?php
namespace Wijaya\WebApp\Admin;
class OldPost extends \Wijaya\WebApp{
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
     * Post Page Middleware(Sessions)
     * @private
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  callable                                 $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($req, $res, $next){
        $req = $req->withAttribute('mw_count', [
            'all'=>$this->pdo->select(['count(post.id)'])->from('post')->join('post_category', 'post.category_id', '=', 'post_category.id')->where('post_category.group_id', '=', $this->group_id)->where('post.deleted', '=', 0)->whereIn('post.status', [ 1, 3 ])->execute()->fetchColumn(),
            'published'=>$this->pdo->select(['count(post.id)'])->from('post')->join('post_category', 'post.category_id', '=', 'post_category.id')->where('post_category.group_id', '=', $this->group_id)->where('post.deleted', '=', 0)->where('post.status', '=', 3)->execute()->fetchColumn(),
            'draft'=>$this->pdo->select(['count(post.id)'])->from('post')->join('post_category', 'post.category_id', '=', 'post_category.id')->where('post_category.group_id', '=', $this->group_id)->where('post.deleted', '=', 0)->where('post.status', '=', 1)->execute()->fetchColumn(),
            'trash'=>$this->pdo->select(['count(post.id)'])->from('post')->join('post_category', 'post.category_id', '=', 'post_category.id')->where('post_category.group_id', '=', $this->group_id)->where('post.deleted', '=', 1)->whereIn('post.status', [ 1, 3 ])->execute()->fetchColumn(),
        ]);
        $res = $next($req, $res);
        return $res;
    }
    /**
     * Get Tag Listed Array
     * @param  integer $post_id post id
     * @return array Tags Name in Array
     */
    public function getTags($post_id){
        $tags = [];
        $tags_select = $this->pdo->select(['tags.name'])->from('tags')->join('post_tags', 'tags.id', '=', 'post_tags.tag_id')->where('post_tags.post_id', '=', $post_id)->execute()->fetchAll();
        foreach($tags_select as $tag){
            array_push($tags, $tag['name']);
        }
        return $tags;
    }
    /**
     * set post tags
     * @param  integer $post_id   post id
     * @param  string  $tagString tags string
     * @return array   Tags Name in Array
     */
    public function setTags($post_id, $tagString){
        $deleteTags = $this->pdo->delete()->from('post_tags')->where('post_id', '=', $post_id)->execute();
        $result = false;
        $tags = explode(',', $tagString);
        foreach($tags as $tag){
            $isTagExist = $this->pdo->select([ 'id' ])->count('id', 'total')->from('tags')->where('name', '=', $tag)->execute()->fetch();
            if($isTagExist['total'] == 0){
                $newTag = $this->pdo->insert([ 'name', 'name_url' ])->into('tags')->values($tag, $this->slug->make($tag))->execute(true);
                if($newTag){
                    $newPostTag = $this->pdo->insert([ 'post_id', 'tag_id' ])->into('post_tags')->value([ $post_id, $newTag ])->execute();
                    $result = $newPostTag;
                }
            }else{
                $newPostTag = $this->pdo->insert(['post_id', 'tag_id'])->into('post_tags')->values([ $post_id, $isTagExist['id'] ])->execute();
                $result = $newPostTag;
            }
        }
        return $result;
    }
    /**
     * Get Posts Data by type
     * @param  string   [$type = 'all'] post status type
     * @return object Posts Data Object
     */
    public function getPostsList($type = 'all'){
        $post_data = [];
        $posts = $this->pdo
            ->select([ "post.id", "post_category.`name` 'category'", 'post.date',  "post_category.name_url", "post.author", "post.`status`", "post.title", "post.title_url", "post.visibility"])
            ->from('post')
            ->join('post_category', 'post.category_id', '=', 'post_category.id')
            ->where('post_category.group_id', '=', $this->group_id);

        switch($type){
            case 'deleted':
                $posts->where('post.deleted', '=', 1)->whereIn('post.status', [ 1, 3 ]);
                break;
            case 'published':
                $posts->where('post.deleted', '=', 0)->where('post.status', '=', 3);
                break;
            case 'draft':
                $posts->where('post.deleted', '=', 0)->where('post.status', '=', 1);
                break;
            default:
                $posts->where('post.deleted', '=', 0)->whereIn('post.status', [ 1, 3 ]);
                break;
        }
        $posts->groupBy('post.id')->orderBy('post.date', 'DESC');
        foreach($posts->execute()->fetchAll() as $post){
            $post['author'] = $this->getUserDetail($this->user_id)['nickname'];
            $post['tags'] = $this->getTags($post['id']);
            array_push($post_data, $post);
        }
        return $post_data;
    }
    /**
     * get post detail by id
     * @param  integer $id post id
     * @return object post data object
     */
    public function getPostById($id){
        $post = $this->pdo->select()->from('post')->where('id', '=', $id)->execute()->fetch();
        $post['tags'] = $this->getTags($id);
        return $post;
    }
    /**
     * get post autosave data by parent post id
     * @param  integer $id parent post id
     * @return object post autosave data object
     */
    public function getAutosavePostById($id){
        $autosave = $this->pdo->select()->from('post')->whereLike('title_url', $id.'-autosave')->execute()->fetch();
        return $autosave;
    }
    /**
     * get post revision data by parent id
     * @param  integer $id parent post id
     * @return object post revisions data object
     */
    public function getRevisionsPostById($id){
        $revisions = $this->pdo->select()->from('post')->whereLike('title_url',  $id.'-revision')->execute()->fetchAll();
        foreach($revisions as $key => $revision){
            $revisions[$key]['authors'] = $this->getUserDetail($revision['author']);
        }
        return $revisions;
    }
    /**
     * update post data by id
     * @param  integer $id   post id
     * @param  object  $data post data
     * @param  integer $type post status type
     * @param  string  $tag  post tags string
     * @return boolean PDO Statement Execute
     */
    public function setPostById($id, $data, $type, $tag){
        $data['status'] = $type;
        $data['title_url'] = $this->slug->make($data['title']);
        $savePost = $this->pdo->update($data)->table('post')->where('id', '=', $id)->execute();
        if($savePost){
            $this->setTags($id, $tag);
        }
        return $savePost;
    }
    /**
     * insert post data
     * @param  boolean $data post data
     * @param  integer $type post status type
     * @param  string  $tag  post tag string
     * @return boolean PDO Statement Execute
     */
    public function setNewPost($data, $type, $tag){
        $data['status'] = $type;
        $data['title_url'] = $this->slug->make($data['title']);
        $savePost = $this->pdo->insert(array_keys($data))->into('post')->values(array_values($data))->execute(true);
        if($savePost){
            $this->setTags($savePost, $tag);
        }
        return $savePost;
    }
    /**
     * update autosave data by parent post id
     * @param  integer  $id           parent post id
     * @param  object   $data         post data
     * @param  integer  $type         post status type
     * @param  boolean  [$done=false] is autosave done?
     * @return boolean  PDO Execute Result
     */
    public function setAutosavePostById($id, $data, $type, $done=false){
        $data['status'] = $type;
        $data['title_url'] = $id.'-autosave'.($done ? '-done': '');
        $deleteAutosave = $this->pdo->delete()->from('post')->whereLike('title_url', $id.'-autosave%')->execute();
        $addAutosave = $this->pdo->insert(array_keys($data))->into('post')->values(array_values($data))->execute(true);
        return $addAutosave;
    }
    /**
     * update post revision data by parent id
     * @param  integer $id parent post id
     * @return object post revisions data object
     */
    public function setRevisionsPostById($id, $data){
        $data['status'] = 5;
        $data['title_url']=$id.'-revision';
        $countRevisions = $this->pdo->select(['count(id)'])->from('post')
            ->where('status', '=', 5)->whereLike('title_url', $id.'-revision')
            ->execute()->fetchColumn();
        if($countRevisions >= 3){
            $oldRevision = $this->pdo->select(['id'])->from('post')
                ->whereLike('title_url', $id.'-revision')
                ->orderBy('id')->limit(1, 0)
                ->execute()->fetchColumn();
            $deleteRevision = $this->pdo->delete()->from('post')->where('id', '=', $oldRevision)->execute();
        }
        $addRevision = $this->pdo->insert(array_keys($data))->into('post')->values(array_values($data))->execute(true);
        return $addRevision;
    }
    /**
     * chage the post status and the autosave
     * @param  float $id     post id
     * @param  integer $status post status in integer
     * @return boolean PDO Execute Result
     */
    public function setPostStatus($id, $status){
        $ChagePostStatus = $this->pdo->update(['status'=>$status])->table('post')->where('id', '=', $id);
        if($ChagePostStatus->execute()){
            $chageAutosaveStatus = $this->pdo->update(['status'=>$status+1])->table('post')->whereLike('title_url', $id.'-autosave%');
            return $chageAutosaveStatus->execute();
        }
    }
    /**
     * chage the post deleted status and the autosave
     * @param  float $id     post id
     * @param  integer $status post deleted status in integer
     * @return boolean PDO Execute Result
     */
    public function setPostDeletedStatus($id, $status){
        $ChagePostStatus = $this->pdo->update(['deleted'=>$status])->table('post')->where('id', '=', $id);
        if($ChagePostStatus->execute()){
            $chageAutosaveStatus = $this->pdo->update(['deleted'=>$status])->table('post')->whereLike('title_url', $id.'-autosave%');
            return $chageAutosaveStatus->execute();
        }
    }
    /**
     * display posts list page
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function displayPostsList($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'all']);
        $req = $req->withAttribute('post_tab', (isset($args['type']) ? $args['type'] : 'all'));
        $req = $req->withAttribute('posts', $this->getPostsList((isset($args['type']) ? $args['type'] : 'all')));
        return $this->view->render($res, 'admin/post/index.html', $req->getAttributes());

    }
    /**
     * display New Post page
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function displayNewPost($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'add']);
        return $this->view->render($res, 'admin/post/add.html', $req->getAttributes());
    }
    /**
     * display Edit Post page
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function displayEditPost($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'l']);
        $req = $req->withAttribute('post_data', $this->getPostById($args['id']));
        $req = $req->withAttribute('post_data_autosave', $this->getAutosavePostById($args['id']));
        $req = $req->withAttribute('post_data_revisions', $this->getRevisionsPostById($args['id']));
        return $this->view->render($res, 'admin/post/edit.html', $req->getAttributes());
    }
    /**
     * display diff Post page
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function displayDiffRevisionsPost($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'diff']);
        $revisions = [];
        foreach($this->getRevisionsPostById($args['id']) as $rev){
            $post = $this->getPostById($args['id']);
            $rev['titleRevToMain'] = $this->diff->render($rev['title'], $post['title']);
            $rev['contentRevToTitle'] = $this->diff->render(strip_tags($rev['content']), strip_tags($post['content']));
            $rev['oldTitle'] = $post['title'];
            $rev['oldContent'] = $post['content'];
            $rev['oldId'] = $post['id'];
            array_push($revisions, $rev);
        }
        $req = $req->withAttribute('post_data_revisions', $revisions);
        return $this->view->render($res, 'admin/post/diff.html', $req->getAttributes());
    }
    /**
     * Actions To Revert the revisions
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return 302                                      HTML Rendered Page
     */
    public function actionsRevertDiff($req, $res, $args){
        $post = $this->getPostById($args['id']);
        foreach($this->getRevisionsPostById($args['id']) as $rev){
            if($rev['id'] == $args['rev_id']){
                $data = [
                    'author'    => $this->user_id,
                    'date'      => date("Y-m-d H:i:s"),
                    'title'     => $rev['title'],
                    'title_url' => $this->slug->make($rev['title']),
                    'content'   => $rev['content']
                ];
                $RevertPost = $this->setPostById($args['id'], $data, $post['status'], implode(', ', $post['tags']));
                if($RevertPost){
                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getAdminPostEditHTML', ['id'=>$args['id']]));
                }
            }
        }
        return $this->view->render($res, 'admin/post/diff.html', $req->getAttributes());
    }
    /**
     * Actions Change Post Status / Post Deleted Status
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return JSON                                     Actions Status
     */
    public function actionsChangePostStatus($req, $res, $args){
        switch($args['action']){
            case 'draft':
                if($this->setPostStatus($args['id'], 1)){
                    $res = $res->withJson(['success'=>true]);
                }
                break;
            case 'publish':
                if($this->setPostStatus($args['id'], 3)){
                    $res = $res->withJson(['success'=>true]);
                }
                break;
            case 'trash':
                if($this->setPostDeletedStatus($args['id'], 1)){
                    $res = $res->withJson(['success'=>true]);
                }
                break;
            case 'revert':
                if($this->setPostDeletedStatus($args['id'], 0)){
                    $res = $res->withJson(['success'=>true]);
                }
                break;
            case 'delete':
                if($this->setPostDeletedStatus($args['id'], 2)){
                    $res = $res->withJson(['success'=>true]);
                }
                break;
        }
        return $res;
    }

    /**
     * Action save changes to post
     * @param  \Psr\Http\Message\ServerRequestInterface $req  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $res  PSR7 response
     * @param  object                                   $args URL Parameter Object
     * @return JSON                                     Actions Status
     */
    public function actionsSavePost($req, $res, $args){
        $data = [
            'category_id'   => $_POST['category'],
            'author'        => $this->session->user_id,
            'date'          => date("Y-m-d H:i:s"),
            'title'         => $_POST['title'],
            'content'       => $_POST['content'],
            'visibility'    => $_POST['visibility'],
            'password'      => md5($_POST['password']),
            'header_image'  => $_POST['image'],
            'deleted'       => 0
        ];
        $response = [];
        if(isset($args['id'])){
            $id = $args['id'];
            switch($_POST['type']){
                case 'draft-autosave':
                    if($this->setAutosavePostById($id, $data, 2)){
                        $response = [ 'status'=>'draft-autosaved', 'id'=>$args['id'] ];
                    }
                    break;
                case 'draft-saved':
                    if($this->setPostById($id, $data, 1, $_POST['tag'])){
                        $this->setAutosavePostById($id, $data, 2, true);
                        $this->setRevisionsPostById($id, $data);
                    }
                    break;
                case 'publish-autosave':
                    if($this->setAutosavePostById($id, $data, 4)){
                        $response = [ 'status'=>'draft-autosaved', 'id'=>$args['id'] ];
                    }
                    break;
                case 'publish-saved':
                    if($this->setPostById($id, $data, 3, $_POST['tag'])){
                        $this->setAutosavePostById($id, $data, 4, true);
                        $this->setRevisionsPostById($id, $data);
                    }
                    break;
            }
        }else{
            $newPost = $this->setNewPost($data, 1, $_POST['tag']);
            if($newPost){
                $this->setAutosavePostById($newPost, $data, 2);
                $response = [ 'status'=>'created', 'id'=>$newPost ];
            }
        }
        $res = $res->withJson($response);
        return $res;
    }

    public function __invoke($req, $res, $args){
        $method = $req->getMethod();
        switch ($method) {
            case 'GET':
                # code...
                break;

            default:
                # code...
                break;
        }
    }
}
