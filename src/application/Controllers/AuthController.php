<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\User as User;
use App\Models\Recover as Recover;
//AuthController Controller Class
class AuthController extends Controller
{
    //Login
    public function getLogin($request, $response, $args)
    {
        $this->view->render($response, 'login.twig');
    }

    public function postLogin($request, $response, $args)
    {
        $data = $request->getParsedBody();

        $this->v->validate([
            'username|Username' => [$data['username'], 'required'],
            'password|Password' => [$data['password'], 'required']
        ]);

        if(!$this->v->passes()) {
            $this->flash->addMessage('error', $this->v->errors()->first());
            return $this->redirect('getLogin');
        }

        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $password = $data['password'];

        //Check if the user exists
        $user = $this->db->table('users')->where('username', '=', $username)->where('confirmed', '1')->first();
        if($user) {
            if(password_verify($password, $user->password))
            {
                //Check if the user is admin
                if($user->access == '1'){
                    $this->session->set('admin', $user->id);
                    return $this->redirect('getHome');
                }
                $this->session->set('uid', $user->id);
                return $this->redirect('getHome');
            }
            $this->flash->addMessage('error', 'The password don\'t match.');
            return $this->redirect('getLogin');
        }

        $this->flash->addMessage('error', 'The username don\'t exists or user is banned.');
        return $this->redirect('getLogin');
        
    }

    //Register
    public function getRegister($request, $response, $args)
    {
        $this->view->render($response, 'register.twig');
    }
    
    public function postRegister($request, $response, $args)
    {
        $data = $request->getParsedBody();

        $this->v->validate([
            'username|Username' => [$data['username'], 'required|min(6)|max(12)'],
            'password|Password' => [$data['password'], 'required|min(6)|max(24)'],
            'password2|Confirm password' => [$data['password2'], 'required|min(6)|max(24)|matches(password)'],
            'email|Email' => [$data['email'], 'required|email'],
            'tos|TOS' => [$data['tos'], 'required']
        ]);

        if(!$this->v->passes()) {
            $this->flash->addMessage('error', $this->v->errors()->first());
            return $this->redirect('getRegister');
        }

        //Get values
        $username = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($data['email'], FILTER_SANITIZE_STRING);
        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        //Check if username exists
        $user = $this->db->table('users')->where('username', '=', $username)->get();
        if($user) {
            $this->flash->addMessage('error', 'The username is taken.');
            return $this->redirect('getRegister');
        }

        //Check if the email exists
        $userEmail = $this->db->table('users')->where('email', '=', $email)->get();
        if($userEmail) {
            $this->flash->addMessage('error', 'The email address is already assigned to an account.');
            return $this->redirect('getRegister');
        }

        User::create([
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'access' => '0',
            'confirmed' => '1'
        ]);

        $this->flash->addMessage('success', 'Your account was created.');
        return $this->redirect('getRegister');
    }


    //Recover
    public function getRecover($request, $response, $args)
    {
        $this->view->render($response, 'recover.twig');
    }

    public function postRecover($request, $response, $args)
    {
        $data = $request->getParsedBody();

        $this->v->validate([
            'email|Email' => [$data['email'], 'required|email']
        ]);

        if(!$this->v->passes()) {
            $this->flash->addMessage('error', $this->v->errors()->first());
            return $this->redirect('getRecover');
        }

        $recover = $this->db->table('users')->where('email', $data['email'])->where('confirmed', '1')->first();
        if(!$recover){
            $this->flash->addMessage('error', 'The email don\'t exist or the account is banned');
            return $this->redirect('getRecover');
        }
        
        $key = md5(uniqid(true));

        Recover::create([
            'uid' => $recover->id,
            'key' => $key,
            'used' => '0'
            ]);

        $link= $request->getUri()->getBaseUrl() . $this->router->pathFor('getChange') . '/' . $key;

        $to = $data['email'];
        $subject = 'Change password';
        $message = '<html><head><title>Change password</title></head><body><p>Hello, please click on the link to reset your password</p><p><a href=' . $link . '>' . $link . '</a></p></body></html>';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: Account <no-replay@>' . "\r\n";

        $send = mail($to, $subject, $message, $headers);

        if(!$send){
            $this->flash->addMessage('error', 'There was an error, please contact the administrator');
            return $this->redirect('getRecover');
        }else{
            $this->flash->addMessage('success', 'Check your mail box');
            return $this->redirect('getLogin');
        }

    }

    public function tos($request, $response, $args)
    {
        $this->view->render($response, 'tos.twig');
    }
    public function getChange($request, $response, $args)
    {
        if(!isset($args['key'])){
            $this->redirect('getHome');
        }

        $key = $args['key'];
        $rec = $this->db->table('recovers')->where('key',$key)->where('used', '0')->first();
        if(!$rec){
            $this->redirect('getHome');
        }

        $user = $this->db->table('users')->where('id',$rec->uid)->first();
        $pass = $this->generate();

        if(!$user){ $this->redirect('getHome'); }

        $to = $user->email;
        $subject = 'Change password';
        $message = '<html><head><title>Change password</title></head><body><p>Hello, here is your new password</p><p>Password: '. $pass .'</p></body></html>';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: Account <no-replay@no-replay>' . "\r\n";

        $send = mail($to, $subject, $message, $headers);

        if(!$send){
            $this->flash->addMessage('error', 'There was an error, please contact the administrator');
            return $this->redirect('getLogin');
        }else{

            $pass2 = password_hash($pass, PASSWORD_DEFAULT);

            User::where('id',$rec->uid)->update(['password' => $pass2]);
            Recover::where('key',$key)->update(['used' => '1']);

            $this->flash->addMessage('success', 'The new password was sent to your email');
            return $this->redirect('getLogin');
        }
    }
    protected function generate($length = 5) {
        $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charLength = strlen($char);
        $rString = '';
        for ($i = 0; $i < $length; $i++) {
            $rString .= $char[rand(0, $charLength - 1)];
        }
        return $rString;
    }

    public function logout($request, $response, $args)
    {
        session_unset();
        session_destroy();
        return $this->redirect('getHome');
    }
}                   