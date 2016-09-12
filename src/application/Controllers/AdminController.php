<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\Setting as Setting;
use App\Models\Report as Report;
use App\Models\User as User;


//AdminController Class
class AdminController extends Controller
{
	//Home Admin
    public function index($request, $response, $args)
    {
    	$users = $this->db->table('users')->orderBy('id', 'desc')->get();
        $this->view->render($response, 'admin/home.twig', ['users' => $users]);
    }

    //Get User
    public function getEdit($request, $response, $args)
    {
    	if(isset($args['key'])){
	    	$key = filter_var($args['key'], FILTER_SANITIZE_STRING);
	    	$user = $this->db->table('users')->where('id', '=', $key)->first();
	    	$images = $this->db->table('images')->where('oid', '=', $key)->where('active', '')->count();
	    	$last = $this->db->table('images')->where('oid', '=', $key)->where('active','0')->orderBy('created_at', 'desc')->take(2)->get();
    	}else{
    		$user = null;
    		$images = null;
    		$last = null;
    	}
    	$this->view->render($response, 'admin/user.twig', ['user' => $user, 'imgs' => $images, 'lasts' => $last]);
    }

    //Search for user 
    public function postSearch($request, $response, $args)
    {
    	$data = $request->getParsedBody();
    	$this->v->validate([
    		'search|Username or email' => [$data['search'], 'required']
    	]);

    	if(!$this->v->passes()){
    		$this->flash->addMessage('error', $this->v->errors()->first());
    		return $this->redirect('AdminHome');
    	}

    	$user = $this->db->table('users')->where('username', '=', $data['search'])->first();

    	if($user){
    		return $this->response->withHeader("Location", $this->router->pathFor('getAdminEdit', ['key' => $user->id]));
    	}

    	$user = $this->db->table('users')->where('email', '=', $data['search'])->first();

	    if($user){
	    	return $this->response->withHeader("Location", $this->router->pathFor('getAdminEdit', ['key' => $user->id]));
	    }

    	$this->flash->addMessage('error', 'The user don\'t exists.');
    	return $this->redirect('AdminHome');
    }

    //Ban
    public function banUser($request, $response, $args)
    {
    	if(isset($args['key'])){
    		User::where('id', '=', $args)->update(['confirmed' => '0']);
    		return $this->redirect('AdminHome');
    	}else{
    		return $this->redirect('AdminHome');
    	}

    }

    //Unban
    public function unBanUser($request, $response ,$args)
    {
    	if(isset($args['key'])){
    		User::where('id', '=', $args)->update(['confirmed' => '1']);
    		return $this->redirect('AdminHome');
    	}else{
    		return $this->redirect('AdminHome');
    	}

    }

    public function getSEO($request, $response, $args)
    {
    	$this->view->render($response, 'admin/SEO.twig');
    }

    public function postSEO($request, $response)
    {
    	$data = $request->getParsedBody();
    	$this->v->validate([
    		'brand|Brand' => [$data['brand'], 'required'],
    		'title|Title' => [$data['title'], 'required'],
    		'description|Description' => [$data['description'], 'required'],
    		'keywords|Keywords' => [$data['keywords'], 'required']
    		]);
    	if(!$this->v->passes()){
    		$this->flash->addMessage('error', $this->v->errors()->first());
    		return $this->redirect('getAdminSeo');
    	}

    	Setting::where('id', '=', '1')->update([
    		'brand' => $data['brand'],
    		'title' => $data['title'],
    		'description' => $data['description'],
    		'keywords' => $data['keywords']
    		]);
    	$this->flash->addMessage('success', 'Updated SEO.');
    	return $this->redirect('getAdminSeo');
    }


    public function getUpload($request, $response, $args)
    {
    	$this->view->render($response, 'admin/upload.twig');
    }	
    public function postUpload($request, $response)
    {
    	$data = $request->getParsedBody();
    	$this->v->validate([
    		'size|Max file size' => [$data['size'], 'required'],
    		'ext|Extensions' => [$data['ext'], 'required'],
    		]);
    	if(!$this->v->passes()){
    		$this->flash->addMessage('error', $this->v->errors()->first());
    		return $this->redirect('getAdminUpload');
    	}

    	$size = $data['size'] * 1000000;

    	Setting::where('id', '=', '1')->update([
    		'max_file_size' => $size,
    		'file_extensions' => $data['ext'],
    		]);
    	$this->flash->addMessage('success', 'Updated settings.');
    	return $this->redirect('getAdminUpload');
    }

    //Reports
    public function getReport($request, $response, $args)
    {
        if(!isset($args['id'])){
            $reports = $this->db->table('reports')->where('active', '1')->orderBy('id', 'desc')->take(40)->get();
            $this->view->render($response, 'admin/report.twig', ['reports' => $reports]);
        }else{
            $report = $this->db->table('reports')->where('id', $args['id'])->where('active', '1')->first();
            $this->view->render($response, 'admin/view.twig', ['report' => $report]);
        }
    }


    public function getClose($request, $response, $args)
    {
        if(!isset($args['id'])){
          return  $this->redirect('getAdminReport');
        }

        Report::where('id', $args['id'])->update(['active' => '0']);
            return $this->redirect('getAdminReport');
    }
}