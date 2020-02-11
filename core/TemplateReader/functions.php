<?php

// TODO see later to remake it better
function dbConnexion()
{
	try
	{
		$db = new PDO('mysql:host=192.168.1.30;charset=utf8', 'root', 'dadfba16');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "CREATE DATABASE IF NOT EXISTS nms";
		$db->exec($sql);
		$db->query("use nms");
		$sql = 'CREATE TABLE IF NOT EXISTS `content` (
				`id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
				`title` varchar(50),
				`type` varchar(20),
				`code_chmod` varchar(3) NOT NULL,
				`slug` varchar(20),
				`date` date,
				PRIMARY KEY (`id`))';
		$db->exec($sql);
				$sql = 'CREATE TABLE IF NOT EXISTS `contentModel` (
				`id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
				`type` varchar(20) NOT NULL,
				`inner` json,
				`order` int UNSIGNED,
				PRIMARY KEY (`id`))';
		$db->exec($sql);
	}
	catch (Exception $e)
	{
		    die('Erreur : ' . $e->getMessage());
	}
	return $db;
}

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

function disconnect($prev, $args)
{
	if (isset($_SESSION['logged']))
	{
		if (isset($_POST['disconnect']))
		{
			session_destroy();
			header('location: ./');
		}
		else
			return '<form action="./" method="post">
						<input type="submit" name="disconnect" value="Se déconnecter" />
					</form>';
	}
	return '';
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

function admMenu($prev, &$args)
{
	$prev = preg_replace('/^..\//', '', $prev);
	$db = dbConnexion();
	$return = '';
	foreach ($db->query('select type from contentModel order by `order`') as $row)
		$return .= '<a href="' . $prev . 'content/' . $row['type'] . '">' . $row['type'] . '</a>';
	return $return;
}

function getContentTitle($prev, &$args)
{
	$prev = preg_replace('/^..\//', '', $prev);
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	return $args[3][2];
}

function addContentLink($prev, &$args)
{
	$prev = preg_replace('/^..\//', '', $prev);
	return '<a href="' . $prev . 'addContent">Add content</a><hr />';
}

function addContent($prev, &$args)
{
	if (isset($_POST['contentTitle']) && isset($_POST['contentOrder']))
	{
		$title = htmlspecialchars($_POST['contentTitle']);
		$order = (int) htmlspecialchars($_POST['contentOrder']) * 10;
		$db = dbConnexion();
		$prepReq = $db->prepare("INSERT INTO contentModel (type, `order`) VALUES (:type, :order)");
		$prepReq->bindParam(':type', $title);
		$prepReq->bindParam(':order', $order);
		$prepReq->execute();
		header('location: ../content/' . $title);
		exit;
	}
	return '';
}

function lastFieldAdded($prev, &$args)
{
	if (isset($_POST['addField']))
	{
		$lastFieldAdded = htmlspecialchars($_POST['addField']);
		$form = '<form method="post" action="./">';
		$form = '<p>Type ' . $lastFieldAdded . '</p>';
		$form .= '<label for="lastFieldAdded" title="It\'s name will be also it\'s class and it\'s id">Name : <input type="' .
			$lastFieldAdded . '" id="lastFieldAdded" name="lastFieldAdded" />';
		$form .= '<br />';
		$form .= '<button type="submit">Validate</button>';
		$form .= '</form>';
		return $form;
	}
}

function listContentInner($prev, &$args)
{
	return 'Content inner list';
}

?>
