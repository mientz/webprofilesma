<?php
class Emailer {
    private $db;
    private $slug;
    private $view;
    private $mail;
    function __construct($container, $mailer, $twig) {
        $this->db = $container->get('db');
        $this->slug = $container->get('slug');
        $this->twig = $twig;
        $this->mail = $mailer;
    }

    function invite($email, $html){
        $this->mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $this->mail->Username = 'demo.sman.1.bangkalan@gmail.com';                 // SMTP username
        $this->mail->Password = 'transistor';                           // SMTP password
        $this->mail->Port = 587;                                    // TCP port to connect to
        $this->mail->setFrom('demo.sman.1.bangkalan@gmail.com', 'SMAN 1 Bangkalan');
        $this->mail->addAddress($email);
        $this->mail->isHTML(true);
        $this->mail->Subject = 'Undangan Akses Manajemen Website PT.STARS Indonesia';
        $this->mail->Body = $html;
        $this->mail->send();
    }
}
