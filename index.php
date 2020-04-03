<?php
	define('__DB__', 'nms');
	define('__DB_PREFIX__', 'nms_');
	define('__DB_HOST__', '192.168.1.30');
	define('__DB_USER__', 'root');
	define('__DB_PASSWORD__', 'dadfba16');
	define('__DEBUG__', true);
	
	if (__DEBUG__)
	{
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
	}

	session_start();
	
	require_once 'core/security/security.php';
	require_once 'core/files/files.php';
	require_once 'core/DBTools/DBTools.php';
	require_once 'core/RequestHandler/RequestHandler.php';
	require_once 'core/Controller/Controller.php';
	require_once 'core/ModuleGlobalController/ModuleGlobalController.php';
	require_once 'core/hooks.php';
	require_once 'core/TemplateReader/TemplateReader.php';

	RequestHandler::getInstance();
	TemplateReader::getInstance()->display();
?>
