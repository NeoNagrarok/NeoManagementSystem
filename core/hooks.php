<?php

//#global
function bodyOn($args)
{
	return '<body id="test">';
}

//#global
function bodyOf($args)
{
	return '</body></html>';
}

//#global
function loadCss($args)
{
	$dir = $args[0] . '/';
	$css = '';
	$cssForeach = [];
	if (file_exists($dir . 'css/order.json'))
	{
		$cssForeach = json_decode(getContentFile($dir . 'css/order.json'));
	}
	else
	{
		$tabDir = getContentDir($dir . 'css/');
		if ($tabDir)
			$cssForeach = $tabDir;
	}
	foreach($cssForeach as $cssFile)
		if (preg_match('/.*\.css$/', $cssFile))
			$css .= '<link rel="stylesheet" href="' . RequestHandler::getPrev() . $dir . 'css/' . $cssFile . '" />';
	return $css;
}

//#global
function loadJs($args)
{
	$dir = $args[0] . '/';
	$js = '';
	$jsForeach = [];
	if (file_exists($dir . 'js/order.json'))
	{
		$jsForeach = json_decode(getContentFile($dir . 'js/order.json'));
	}
	else
	{
		$tabDir = getContentDir($dir . 'js/');
		if ($tabDir)
			$jsForeach = $tabDir;
	}
	foreach($jsForeach as $jsFile)
		if (preg_match('/.*\.js$/', $jsFile))
			$js .= '<script src="' . RequestHandler::getPrev() . $dir . 'js/' . $jsFile . '"></script>';
	return $js;
}

//#global
function ifRank(&$args)
{
	$rank = explode('/', $args[0])[0];
	if (end($args[1]) === 'true')
	{
		$args[1][$rank] = 'false';
		if (isset($_SESSION['rank']) && $_SESSION['rank'] === $rank)
			$args[1][$rank] = 'true';
	}
	return '';
}

//#global
function fi(&$args)
{
	$rank = explode('/', $args[0])[0];
	if (isset($args[1][$rank]))
	{
		while (array_key_last($args[1]) != $rank)
			unset($arg[1][array_key_last($args[1])]);
		unset($args[1][$rank]);
	}
	return '';
}

//#global
function  removeRefreshBehavior(&$args)
{
	include_once 'core/form/form.php';
	return setInputRandomSession();
}
?>
