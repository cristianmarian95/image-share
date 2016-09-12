<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\User as User;
use App\Models\Image as Image;

//User Dashboard Controller Class
class DashController extends Controller
{
    public function index($request, $response, $args)
    {	
    	if($this->session->get('uid')){
    		$images = $this->db->table('images')->where('oid', '=', $this->session->get('uid'))->where('active', '0')->get();
    	}
    	if($this->session->get('admin')){
    		$images = $this->db->table('images')->where('oid', '=', $this->session->get('admin'))->where('active', '0')->get();
    	}
    	$this->view->render($response, 'account.twig', ['images' => $images]);
    }

    public function getSettings($request, $response, $args)
    {
        $this->view->render($response, 'settings.twig');
    }

    public function postUpdate($request, $response, $args)
    {
        $data = $request->getParsedBody();

        if(!isset($data['update'])){
            $this->flash->addMessage('error', 'There was an error, please contact the administrator.');
            return $this->redirect('getAccountSettings');
        }

        //Check the update request
        if($data['update'] == 'pass'){
            $this->v->validate([
                'newpassword|New password' => [$data['newpass'], 'required|min(6)|max(24)'],
                'password2|Confirm password' =>[$data['repass'], 'required|min(6)|max(24)|matches(newpassword)'],
                'pass|Current password' => [$data['pass'], 'required']
            ]);

            if(!$this->v->passes()){
                $this->flash->addMessage('error', $this->v->errors()->first());
                return $this->redirect('getAccountSettings');
            }

            if($this->session->get('uid')){
                $uid = $this->session->get('uid');
            }elseif($this->session->get('admin')){
                $uid = $this->session->get('admin');
            }

            $user = $this->db->table('users')->where('id', $uid)->first();

            if(password_verify($data['pass'], $user->password)){

                $pass = password_hash($data['newpass'], PASSWORD_DEFAULT);

                User::where('id', $uid)->update(['password' => $pass]);
                $this->flash->addMessage('success', 'Account updated.');
                return $this->redirect('getAccountSettings');
            }

            $this->flash->addMessage('error', 'The password don\'t match.');
            return $this->redirect('getAccountSettings');
        }

        if($data['update'] == 'email'){
            $this->v->validate([
                'email|Email' => [$data['email'], 'required|email'],
                'pass|Curent password' => [$data['pass'], 'required']
            ]);

            if(!$this->v->passes()){
                $this->flash->addMessage('error', $this->v->errors()->first());
                return $this->redirect('getAccountSettings');
            }

            if($this->session->get('uid')){
                $uid = $this->session->get('uid');
            }elseif($this->session->get('admin')){
                $uid = $this->session->get('admin');
            }

            $s = $this->db->table('users')->where('id', $uid)->first();

            if($s->email == $data['email']){
                $this->flash->addMessage('error', 'You can\'t change the email: ' . $data['email'] . ' to ' . $data['email'] . '.');
                return $this->redirect('getAccountSettings');
            }

            $email = $this->db->table('users')->where('email', '=', $data['email'])->first();
            if($email->email){
                $this->flash->addMessage('error', 'The email address is already assigned to an account.');
                return $this->redirect('getAccountSettings');
            }

            $user = $this->db->table('users')->where('id', $uid)->first();
            if(password_verify($data['pass'], $user->password)){
                User::where('id', $uid)->update(['email' => $data['email']]);
                $this->flash->addMessage('success', 'Account updated.');
                return $this->redirect('getAccountSettings');
            }

            $this->flash->addMessage('error', 'The password don\'t match.');
            return $this->redirect('getAccountSettings');
        }

        $this->flash->addMessage('error', 'There was an error, please contact the administrator.');
        return $this->redirect('getAccountSettings');
    }

    public function getDelete($request, $response, $args)
    {
        if(!isset($args['key'])){
            return $this->redirect('getAccount');
        }

        $img = $this->db->table('images')->where('name', $args['key'])->where('active', '0')->first();

        if(!$img){
            return $this->redirect('getAccount');
        }

        if($img->oid == $this->session->get('uid')){
            Image::where('name', $args['key'])->update(['active' => '1']);
            return $this->redirect('getAccount');
        }
        if($this->session->get('admin')){
            Image::where('name',$args['key'])->update(['active' => '1']);
            return $this->redirect('getAccount');
        }
        return $this->redirect('getAccount');

    }
}