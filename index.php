<?php
	define('__DB__', 'nms');
	define('__DB_PREFIX__', 'nms_');
	define('__DB_HOST__', '192.168.1.30');
	define('__DB_USER__', 'root');
	define('__DB_PASSWORD__', 'dadfba16');
	define('__DEBUG__', true);

	session_start();
	
	include_once 'core/RequestHandler/RequestHandler.php';
	include_once 'core/TemplateReader/TemplateReader.php';
	
	if (__DEBUG__)
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}
	
	$TemplateReader = new TemplateReader();
	$RequestHandler = new RequestHandler();
	$RequestHandler->display($TemplateReader);
?>
