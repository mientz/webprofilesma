<?php
$app->group('/contacts', function () {

    $this->get('', function ($req, $res, $args) {
        $req = $req->withAttribute('captcha_error', $this->flash->getMessage('captcha_error'));
        $req = $req->withAttribute('form_data', $this->flash->getMessage('form_data'));
        $req = $req->withAttribute('mesages_save', $this->flash->getMessage('mesages_save'));
        return $this->view->render($res, 'web/contacts.html', $req->getAttributes());
    })->setName('getContactsHTML');

    $this->post('', function ($req, $res, $args) {
        $settings = $req->getAttribute('settings');
        $reCaptcha = $settings['rcs'];
        if(isset($_POST['g-recaptcha-response'])){
            $postdata = http_build_query([

                'secret' => $reCaptcha['sck'],
                'response' => $_POST['g-recaptcha-response']

            ]);
            $opts = [
                'http'=>[
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                ]
            ];
            $context  = stream_context_create($opts);
            $verify=file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context);
            $response = json_decode($verify, true);
            if($response['success'] == false){
                $this->flash->addMessage('captcha_error', 'Terjadi kesalahan pada CAPTCHA');
                $this->flash->addMessage('form_data', $_POST);
            }else{
                $saveMessages = $this->pdo
                    ->insert([
                        'date', 'data', 'status'
                    ])
                    ->into('messages')
                    ->values([
                        date("Y-m-d H:i:s"),
                        json_encode($_POST),
                        0
                    ])
                    ->execute();
                if($saveMessages){
                    $this->flash->addMessage('mesages_save', true);
                }
            }
        }
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('getContactsHTML'));
    })->setName('postContacts302');
})->add($global);
