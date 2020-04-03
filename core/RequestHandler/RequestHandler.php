<?php

class RequestHandler
{
	private function __construct()
	{
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$this->route = substr($_SERVER['REQUEST_URI'], strlen($basepath));
		$this->parameters = '';
		if (strstr($this->route, '?'))
		{
			$this->getParameters = substr($this->route, strpos($this->route, '?') + 1, strlen($this->route));
			$this->route = substr($this->route, 0, strpos($this->route, '?'));
		}
		$this->route = '/' . trim($this->route, '/');
		$this->tabRoute = array();
		$routes = explode('/', $this->route);
		$this->prev = '';
		foreach($routes as $route)
			trim($route) != '' && array_push($this->tabRoute, $route) && $this->prev .= '../';
	}

	public static function getInstance()
	{
		if(is_null(self::$singleton))
			self::$singleton = new RequestHandler();  
		return self::$singleton;
	}

	public static function getTabRoute()
	{
		return self::$singleton->tabRoute;
	}

	public static function getPrev()
	{
		return self::$singleton->prev;
	}

	public static function getParameters()
	{
		return self::$singleton->parameters;
	}

	private $route; /* All levels of route : /test/toto/ok/ */
	private $parameters; /* string with all parameters */
	private $tabRoute; /* Each level of url : /test/toto/ok => [0] = test [1] = toto etc. */
	private $prev; /* ../ or ../../ or ../../../ etc. */
	
	private static $singleton = null;
}

?>
