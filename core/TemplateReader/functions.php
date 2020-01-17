<?php

function bodyOn($prev, $args)
{
	return '<body id="test">';
}

function bodyOf($prev, $args)
{
	return '</body></html>';
}

function loadCss($prev, $args)
{
	$dir = $args[0] . '/';
	$css = '';
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
			$css .= '<link rel="stylesheet" href="' . $prev . $dir . 'css/' . $cssFile . '" />';
	return $css;
}

function loadJs($prev, $args)
{
	$dir = $args[0] . '/';
	$js = '';
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
			$js .= '<script src="' . $prev . $dir . 'js/' . $jsFile . '"></script>';
	return $js;
}

function connect($prev, $args)
{
//	session_destroy();
	if (explode('/', $args[0])[0] === 'admin' && !isset($_SESSION['logged']))
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
			$TemplateReader = $args[2];
			echo $TemplateReader->parser(getContentFile($args[0] . '/metaHead.tpl')) . bodyOn($prev, $args) . $TemplateReader->parser(getContentFile($args[0] . '/parts/connectForm.tpl')) . bodyOf($prev, $args);
			exit();
		}
		else
		{
			$adId = htmlspecialchars($_POST['adId']);
			$adPw = htmlspecialchars($_POST['adPw']);
			$log = $_SERVER['REMOTE_ADDR'] . '>' . date('Y-m-d H:i:s') . '>' . $adId .'>';
			/* TODO get real identifiants from the place where data are stored ! */
			$logged = true;
			if ($adId !== 'Test' || $adPw !== 'test')
				$logged = false;
			else
			{
				$_SESSION['logged'] = str_shuffle(random_bytes(8) . (~$adId) . random_bytes(8) . ~($adPw) . random_bytes(8));
				$_SESSION['rank'] = 'admin'; // TODO Get the right admin rank where data are stored
				/* TODO create good sessions corresponding to good permissions by example */
			}
			$log .= ($logged ? 'true' : 'false') . "\n";
			createFile('logs/connect.log', $log);
			if (!$logged)
				header('location: ./?error=1');
		}
	}
}

function ifRank($prev, &$args)
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

function fi($prev, &$args)
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

?>
