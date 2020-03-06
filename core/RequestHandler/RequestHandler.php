<?php

include_once 'core/files/files.php';

class RequestHandler
{
	public function __construct()
	{
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$this->route = substr($_SERVER['REQUEST_URI'], strlen($basepath));
		$this->getParameters = '';
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
		{
			if(trim($route) != '')
			{
				array_push($this->tabRoute, $route);
				$this->prev .= '../';
			}
		}
	}

	public function getRouteTab()
	{
		return $this->routeTab;
	}

	public function getPrev()
	{
		return $this->prev;
	}

	public function display($TemplateReader)
	{
		/* TODO may be we can define context and pages in other php file which can used as configuration file ???
		$tabContext['admin'] = 'admin'; /* [routeVar] = 'folder where to search index.tpl' */
		$tabContext['install'] = 'install';
		$context = 'themes'; /* Default folder where to search index.tpl */
		
		/* TODO get the good list of pages from where data are stored !!! */
		$tabPages['themes']['home'] = 'tpl';
		$tabPages['themes']['adminTest'] = 'tpl';
		$tabPages['admin']['home'] = 'tpl';
		$tabPages['admin']['addContent'] = 'tpl';
		$tabPages['admin']['content'] = 'tpl';
		$tabPages['install']['home'] = 'tpl';
		$page = 'home';
		
		/* TODO */
		$theme = 'defaultTheme';
		/* TODO put here the code to choose an other theme if we had set an other theme for our cms ! */
		
		if (isset($this->tabRoute[0]))
		{
			if (isset($tabContext[$this->tabRoute[0]]))
				$context = $tabContext[$this->tabRoute[0]];
			if ($this->tabRoute[0] == 'theme')
				$page = $this->tabRoute[0];
			else
				$theme = 'default' . ucfirst($context) . 'Theme';
				/* TODO put here the code to choose an other admin theme if we had set an other theme for our cms ! */
		}
		$mainTpl = $context . '/' . $theme . '/index.tpl';
		if (isset($this->tabRoute[1]))
			$page = $this->tabRoute[1];
		if (isset($tabPages[$context][$page]))
			$page = $page . '.' . $tabPages[$context][$page];
		else
			$page = '404.tpl';
		$TemplateReader->setPrev($this->prev);
		$TemplateReader->setTabRoute($this->tabRoute);
		$TemplateReader->setPage($context, $page, $theme);
//		echo htmlspecialchars($TemplateReader->getHTML($context, $mainTpl));
		echo $TemplateReader->getHTML($context, $mainTpl);
	}

	private $route; /* All levels of route : /test/toto/ok/ */
	private $getParameters; /* string with all parameters */
	private $tabRoute; /* Each level of url : /test/toto/ok => [0] = test [1] = toto etc. */
	private $prev; /* ../ or ../../ or ../../../ etc. */
}

?>
