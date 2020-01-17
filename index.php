<?php
	session_start();
	
	include_once 'core/RequestHandler/RequestHandler.php';
	include_once 'core/TemplateReader/TemplateReader.php';
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	$TemplateReader = new TemplateReader();
	$RequestHandler = new RequestHandler();
	$RequestHandler->display($TemplateReader);
?>
