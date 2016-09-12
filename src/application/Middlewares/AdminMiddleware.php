<?php

namespace App\Middlewares;

use App\Middleware as Middleware;


class AdminMiddleware extends Middleware 
{
	public function __invoke($request, $response, $next)
	{
		if(!$this->session->get('admin'))
        {
            return $this->redirect('getHome');
        }

        $response = $next($request, $response);

        return $response;
	}
}