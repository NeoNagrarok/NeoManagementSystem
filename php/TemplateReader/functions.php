<?php

function bodyOn($prev)
{
	return '<body id="test">';
}

function bodyOf($prev)
{
	return '</body></html>';
}

function loadCss($prev)
{
	$css = '';
	$tabDir = getContentDir('css/');
	if ($tabDir)
		foreach($tabDir as $cssFile)
			if (preg_match('/.*\.css$/', $cssFile))
				$css .= '<link rel="stylesheet" href="' . $prev . 'css/' . $cssFile . '" />';
	return $css;
}

function loadJs($prev)
{
	// TODO add json fihome/le or other solution in order to know in advance the load order, else it may cause some problem in term of depency.
	$js = '';
	$tabDir = getContentDir('js/');
	foreach($tabDir as $jsFile)
		if (preg_match('/.*\.js$/', $jsFile))
			$js .= '<script src="' . $prev . 'js/' . $jsFile . '"></script>';
	return $js;
}

?>
