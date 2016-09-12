<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\User as User;
use App\Models\Setting as Setting;

//Install Controller Class
class InstallController extends Controller
{
	//Install Route
    public function index($request, $response, $args)
    {
    	if(!$this->db->schema()->hasTable('users', 'images', 'reports', 'settings', 'recovers')) {
    		$this->session->set('install', '1');
			return $response->withRedirect($this->router->pathFor('getInstall'));
		}else{
			return $response->withRedirect($this->router->pathFor('getHome'));
		}
    }

    //Welcome Install
	public function welcome($request, $response, $args)
    {
        if(!$this->session->get('install')){
            return $response->withRedirect($this->router->pathFor('getHome'));
        }
        $this->view->render($response, 'install/welcome.twig');
    }    


    //Install Database
    public function getStep1($request, $response, $args) 
    {
        if(!$this->session->get('install')){
            return $response->withRedirect($this->router->pathFor('getHome'));
        }
        $this->view->render($response, 'install/step1.twig');

        //DataBase Install Users
        $this->db->schema()->create('users', function($table){
            $table->increments('id');
            $table->string('username', 256);
            $table->string('password', 256);
            $table->string('email', 256);
            $table->boolean('access');
            $table->boolean('confirmed');
            $table->timestamps();
        });

        //DataBase Install Images
        $this->db->schema()->create('images', function($table){
            $table->increments('id');
            $table->string('oid', 256);
            $table->string('owner', 256);
            $table->string('name', 256);
            $table->string('link', 256);
            $table->string('size', 256);
            $table->boolean('active');
            $table->timestamps();
        });

        //Database Install Resports
        $this->db->schema()->create('reports', function($table){
            $table->increments('id');
            $table->string('link', 256);
            $table->string('email', 256);
            $table->longtext('report', 256);
            $table->boolean('active');
            $table->timestamps();
        });

        //Recover
        $this->db->schema()->create('recovers', function($table){
            $table->increments('id');
            $table->string('uid', 256);
            $table->string('key', 256);
            $table->boolean('used');
            $table->timestamps();
        });

        //Website settings
        $this->db->schema()->create('settings', function($table){
            $table->increments('id');
            $table->string('title', 256);
            $table->longtext('keywords');
            $table->longtext('description');
            $table->string('brand', 256);
            $table->boolean('account_activation');
            $table->string('max_file_size', 256);
            $table->string('file_extensions', 256);
            $table->longtext('website_tos');
            $table->timestamps();
        });
        return $response->withRedirect($this->router->pathFor('getStep2'));
    }


    //Admin Account
    public function getStep2($request, $response, $args) 
    {
        if(!$this->session->get('install')){
            return $response->withRedirect($this->router->pathFor('getHome'));
        }
        $flash = '';
        if(isset($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        $this->view->render($response, 'install/step2.twig', ['flash' => $flash]);
    }

    public function postStep2($request, $response, $args) 
    {
        $data = $request->getParsedBody();
        $user_data = [];
        $user_data['username'] = filter_var($data['username'], FILTER_SANITIZE_STRING);
        $user_data['password'] = filter_var($data['password'], FILTER_SANITIZE_STRING);
        $user_data['password2'] = filter_var($data['password2'], FILTER_SANITIZE_STRING);
        $user_data['email'] = $data['email'];

        if(empty($user_data['username']) || empty($user_data['password']) || empty($user_data['password2']) || empty($user_data['email'])){
            $_SESSION['flash'] = "All the forms need filed";
            return $response->withRedirect($this->router->pathFor('getStep2'));
        }

        if($user_data['password'] !== $user_data['password2']){
            $_SESSION['flash'] = "The password don't mach";
            return $response->withRedirect($this->router->pathFor('getStep2'));
        }

        if(!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)){
            $_SESSION['flash'] = "Enter a valide email";
            return $response->withRedirect($this->router->pathFor('getStep2'));
        }

        User::create([
            'username' => $user_data['username'],
            'password' => password_hash($user_data['password'], PASSWORD_DEFAULT),
            'email' => $user_data['email'],
            'access' => '1',
            'confirmed' => '1'
            ]);
        return $response->withRedirect($this->router->pathFor('getStep3'));

    }


    //Website Settings
    public function getStep3($request, $response, $args) 
    {
        if(!$this->session->get('install')){
            return $response->withRedirect($this->router->pathFor('getHome'));
        }
        $this->view->render($response, 'install/step3.twig');
    }

    public function postStep3($request, $response, $args) 
    {
        
        $data = $request->getParsedBody();
        $settings = [];
        $settings['title'] = filter_var($data['title'], FILTER_SANITIZE_STRING);
        $settings['description'] = filter_var($data['description'], FILTER_SANITIZE_STRING);
        $settings['keywords'] = filter_var($data['keywords'], FILTER_SANITIZE_STRING);

        if(empty($settings['title'])){
            $settings['title'] = "Edit title from ACP";
        }
        if(empty($settings['description'])){
            $settings['description'] = "Edit description from ACP";
        }
        if(empty($settings['keywords'])){
            $settings['keywords'] = "Edit keywords from ACP";
        }

        Setting::create([
            'title' => $settings['title'],
            'description' => $settings['description'],
            'keywords' => $settings['keywords'],
            'brand' => 'ImageHosting', // Logo by default
            'account_activation' => 0, // No cativation require
            'max_file_size' => '5000000', // 5MB by default
            'file_extensions' => 'jpg,jpeg,png,bmp,gif',
            'webiste_tos' => 'Edit the website tos from ACP'
            ]);
        return $response->withRedirect($this->router->pathFor('finish'));
    }

    //Finish
    public function finish($request, $response, $args) 
    {
        if(!$this->session->get('install')){
            return $response->withRedirect($this->router->pathFor('getHome'));
        }

        $this->view->render($response, 'install/finish.twig');
        $this->session->delete('install');
    }


}