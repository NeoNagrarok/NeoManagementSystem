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
	// TODO add json fihome/le or other solution in order to know in advance the load order, else it may cause some problem in term of depency.
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
	// TODO add json fihome/le or other solution in order to know in advance the load order, else it may cause some problem in term of depency.
	$dir = $arg . '/';
	$js = '';
	$tabDir = getContentDir($dir . 'js/');
	foreach($tabDir as $jsFile)
		if (preg_match('/.*\.js$/', $jsFile))
			$js .= '<script src="' . $prev . $dir . 'js/' . $jsFile . '"></script>';
	return $js;
}

function session($prev, $arg)
{
	session_start();
}

function connect($prev, $arg)
{
//	session_destroy();
	if (explode('/', $arg)[0] === 'admin' && !isset($_SESSION['logged']))
	{
		if (!isset($_POST['adId']) || !isset($_POST['adPw']))
		{
			/* TODO get the good error message depending on the language settled ! */
			$connectForm = '';
			if (isset($_GET['error']))
			{
				$arrayError[1] = 'Mauvais identifiants !';
				
				if (isset($arrayError[$_GET['error']]))
					$connectForm .= '<p>' . $arrayError[$_GET['error']] . '</p>';
			}
			$TemplateReader = new TemplateReader();
			$connectForm .= $TemplateReader->parser(getContentFile($arg . '/parts/connectForm.tpl'));
			echo $connectForm;
			exit();
		}
		else
		{
			$adId = htmlspecialchars($_POST['adId']);
			$adPw = htmlspecialchars($_POST['adPw']);
			$log = $_SERVER['REMOTE_ADDR'] . '>' . date('Y-m-d H:i:s') . '>' . $adId .'>';
			/* TODO get real identifiants from the pace where data are stored ! */
			$logged = true;
			if ($adId !== 'Test' || $adPw !== 'test')
				$logged = false;
			else
				$_SESSION['logged'] = str_shuffle(random_bytes(8) . (~$adId) . random_bytes(8) . ~($adPw) . random_bytes(8));
			$log .= ($logged ? 'true' : 'false') . "\n";
			createFile('logs/connect.log', $log);
			if (!$logged)
				header('location: ./?error=1');
		}
	}
}

?>
