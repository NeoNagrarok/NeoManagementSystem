<?php
	include_once 'php/RequestHandler/RequestHandler.php';
	include_once 'php/TemplateReader/TemplateReader.php';
	
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	
	$TemplateReader = new TemplateReader();
	$RequestHandler = new RequestHandler();
	$RequestHandler->display($TemplateReader);
?>
