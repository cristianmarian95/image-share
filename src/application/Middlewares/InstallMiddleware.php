<?php

namespace App\Middlewares;

use App\Middleware as Middleware;


class InstallMiddleware extends Middleware 
{
	public function __invoke($request, $response, $next)
	{
		if(!$this->db->schema()->hasTable('users', 'images', 'reports', 'settings', 'recovers')) {
    		$this->session->set('install', '1');
			return $response->withRedirect($this->router->pathFor('getInstall'));
		}

        $response = $next($request, $response);

        return $response;
	}
}