<?php
class Emailer {
    private $db;
    private $slug;
    private $view;
    private $mail;
    function __construct($container, $mailer) {
        $this->db = $container->get('db');
        $this->slug = $container->get('slug');
        $this->view = $container->get('view');
        $this->mail = $mailer;
    }

    function invite($email, $privilege, $group){

    }
}
