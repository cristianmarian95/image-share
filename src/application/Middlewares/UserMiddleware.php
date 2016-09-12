<?php

namespace App\Middlewares;

use App\Middleware as Middleware;


class UserMiddleware extends Middleware 
{
	public function __invoke($request, $response, $next)
	{
		if(!$this->session->get('uid')){
        	if(!$this->session->get('admin')) {
            	return $this->redirect('getHome');
        	}
        }

        $response = $next($request, $response);
        return $response;
	}
}