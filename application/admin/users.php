<?php

class Users extends Admin{
    /*
     * This Class Only Variable
     */
    protected $ci;
    /**
     * Constructor
     * @private
     * @param function $ci Slimm Container Interface
     */
    public function __construct($ci) {
        $this->ci = $ci;
        parent::__construct($ci);
    }
    /**
     * Get User List
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  object                                   $args     URL Parameter Object
     * @return HTML                                     HTML Rendered Page
     */
    public function getUserLists($req, $res, $args) {
        $req = $req->withAttribute('sidemenu', ['users'=>'list']);
        $select = $this->db->prepare("select * from users where deleted='0'");
        if($select->execute()){
            $users = [];
            foreach($select->fetchAll(PDO::FETCH_ASSOC) as $user){
                $groups = $this->db->prepare("select groups.id, groups.name from user_group, groups where user_group.group_id=groups.id and user_group.user_id=:user_id");
                $groups->bindValue(':user_id', $user['id'], PDO::PARAM_INT);
                $groups->execute();
                $user['status'] = json_decode($user['status']);
                $user['privilege'] = json_decode($user['privilege']);
                $user['groups'] = $groups->fetchAll(PDO::FETCH_ASSOC);
                array_push($users, $user);
            }
            $req = $req->withAttribute('users', $users);
        }
        //        $this->mailer->invite('genthowijaya@gmail.com', $this->view->render($res, 'email/invite.html', $req->getAttributes()));
        return $this->view->render($res, 'admin/users.html', $req->getAttributes());
    }

}
