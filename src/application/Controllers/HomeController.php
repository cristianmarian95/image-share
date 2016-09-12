<?php
//Set NameSpace
namespace App\Controllers;

//Use
use App\Controller as Controller;
use App\Models\Report as Report;

//HomeController Class
class HomeController extends Controller
{
    public function index($request, $response, $args)
    {
    	if(!$this->db->schema()->hasTable('users', 'images', 'reports', 'settings')) {
    		$this->session->set('install', '1');
			return $response->withRedirect($this->router->pathFor('getInstall'));
		}

        $this->view->render($response, 'home.twig');
    }

    public function viewimg($request, $response, $args)
    {
        $key = filter_var($args['key'], FILTER_SANITIZE_STRING);
        if(empty($key)){
            return $this->redirect('getHome');
        }
        $keys = explode(',', $key);
        foreach ($keys as $name) {
            $image[] = $this->db->table('images')->where('name', '=', $name)->where('active', '=', '0')->first();
        }
        $this->view->render($response, 'view.twig', ['imgs' => $image]);
    }

    public function getReport($request, $response, $args)
    {
        if(!isset($args['key'])){
            return $this->redirect('getHome');
        }

        $img = $this->db->table('images')->where('name', $args['key'])->first();
        if(!$img){
            return $this->redirect('getHome');
        }
        $this->view->render($response, 'report.twig', ['name' => $img->name]);
    }

    public function postReport($request, $response, $args)
    {
        $data = $request->getParsedBody();

        if(empty($data['name'])){
            $this->redirect('getHome');
        }

        $this->v->validate([
            'username|Username' => [$data['username'], 'required'],
            'email|Email' => [$data['email'], 'required'],
            'message|Message' => [$data['msg'], 'required'],
        ]);

        if(!$this->v->passes()){
            $this->flash->addMessage('error', $this->v->errors()->first());
            return $this->response->withHeader("Location", $this->router->pathFor('getReport', ['key' => $data['name']]));
        }

        $img = $this->db->table('images')->where('name', $data['name'])->where('active', '0')->first();

        if(!$img){
            $this->flash->addMessage('error', 'The image don\'t exists.');
            return $this->response->withHeader("Location", $this->router->pathFor('getReport', ['key' => $data['name']]));
        }

        Report::create([
            'link' => $data['name'],
            'email' => $data['username'] . ',' .$data['email'],
            'report' => $data['msg'],
            'active' => '1'
            ]);
        $this->flash->addMessage('success', 'Report sent.');
        return $this->response->withHeader("Location", $this->router->pathFor('getReport', ['key' => $data['name']]));

    }
    
}