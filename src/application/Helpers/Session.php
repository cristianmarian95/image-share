<?php

namespace App\Helpers;

class Session 
{
	protected $temp = [];

	public function set($key, $value)
	{
		$_SESSION[$key] = $value;
	}

	public function get($key)
	{
		if($this->exists($key)) {
			return $_SESSION[$key];
		} 
	}

	public function exists($key)
	{
		return isset($_SESSION[$key]);	
	}

	public function delete($key)
	{
	    if($this->exists($key)) {
			session_unset($_SESSION[$key]);
		}
	}
}