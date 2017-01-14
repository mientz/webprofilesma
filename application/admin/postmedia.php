<?php
class Postmedia extends WebApp{
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
    public function setPostmedia($data){
        return $this->pdo->insert(array_keys($data))->into('postmedia')->values(array_values($data))->execute();
    }
    public function getPostmedia(){
        $postmedia = $this->pdo->select()->from('postmedia')->where('group_id', '=', $this->group_id)->orderBy('date', 'DESC')->execute()->fetchAll();
        return $postmedia;
    }
    public function removePostmedia($id, $file){
        $postmedia = $this->pdo->delete()->from('postmedia')->where('id', '=', $id)->execute();
        if($postmedia){
            unlink($file);
        }
        return $postmedia;
    }

    public function actionsUploadPostmedia($req, $res, $args){
        $file = $req->getUploadedFiles()['file'];
        $allowedMime = [
            "image/bmp",
            "image/png",
            "image/jpeg",
            "image/gif"
        ];
        if(in_array($file->getClientMediaType(), $allowedMime)){
            $filename = $this->slug->file($file->getClientFilename());
            $name = str_replace(".".end((explode(".", $filename))),"",$filename);
            $ext = end(explode(".", $filename));
            if(file_exists("public/content/".$name.'.'.$ext)){
                $name = rand().$name;
            }
            $data = [
                'group_id'  => $this->group_id,
                'name'      => $name,
                'date'      => date("Y-m-d H:i:s"),
                'author'    => $this->user_id,
                'mime'      => $ext,
            ];
            if($this->setPostmedia($data)){
                $file->moveTo("public/content/".$name.'.'.$ext);
                $res = $res->withJson([
                    "success" => true
                ]);
            }
        }else{
            $res = $res->withJson([
                "failed" => true
            ]);
        }
        return $res;
    }

    public function displayGetPostmedia($req, $res, $args){
        $req = $req->withAttribute('sidemenu', ['post'=>'media']);
        $file = [];
        foreach($this->getPostmedia() as $data){
            $data['dateString'] = date("d F Y", strtotime($data['date']));
            $data['author'] = $this->db->query("select nickname from users where id='".$data['author']."'")->fetchColumn();
            array_push($file, $data);
        }
        $req = $req->withAttribute('postmedia', $file);
        return $this->view->render($res, 'admin/post/media.html', $req->getAttributes());
    }

    public function actionsGetPostmedia($req, $res, $args){
        $file = [];
        foreach($this->getPostmedia() as $data){
            $data['dateString'] = date("d F Y", strtotime($data['date']));
            $data['author'] = $this->db->query("select nickname from users where id='".$data['author']."'")->fetchColumn();
            array_push($file, $data);
        }
        $res = $res->withJson([
            "postmedia" => $file
        ]);
        return $res;
    }

    public function actionsDeletePostmedia($req, $res, $args){
        if($this->removePostmedia($args['id'], $args['file'])){

            $res = $res->withJson([
                "success" => true
            ]);
        }
        return $res;
    }
}
