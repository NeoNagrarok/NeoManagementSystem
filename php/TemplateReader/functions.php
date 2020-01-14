<?php

function bodyOn($prev, $arg)
{
	return '<body id="test">';
}

function bodyOf($prev, $arg)
{
	return '</body></html>';
}

function loadCss($prev, $arg)
{
	$dir = '';
	if ($arg)
		$dir = $arg . '/';
	$css = '';
	$tabDir = getContentDir($dir . 'css/');
	if ($tabDir)
		foreach($tabDir as $cssFile)
			if (preg_match('/.*\.css$/', $cssFile))
				$css .= '<link rel="stylesheet" href="' . $prev . $dir . 'css/' . $cssFile . '" />';
	return $css;
}

function loadJs($prev, $arg)
{
	$dir = '';
	if ($arg)
		$dir = $arg . '/';
	// TODO add json fihome/le or other solution in order to know in advance the load order, else it may cause some problem in term of depency.
	$js = '';
	$tabDir = getContentDir('js/');
	foreach($tabDir as $jsFile)
		if (preg_match('/.*\.js$/', $jsFile))
			$js .= '<script src="' . $prev . $dir . 'js/' . $jsFile . '"></script>';
	return $js;
}

?>
