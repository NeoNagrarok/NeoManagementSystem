<?php

//#tpl install
function start()
{
	include_once 'core/security/security.php';
	if (!htmlget(${$remove = 'remove'}))
	{
		if (htmlget(${$start = 'start'}))
		{
			if (!DBTools::isInstalled())
			{
				$dbObj = DBTools::getInstance();
				$dbObj->install();
				$dbObj->defaultLanguageSetting();
				$dbObj->defaultTheme();
				$dbObj->installDone();
				return '<a href="?">Back</a><br />installed<br />
					<a href="/admin">Back office</a><br />
					';
			}
			header('location: ./');
			exit;
		}
		return '<a href="?start=">Install</a><br />';
	}
	return '';
}

//#tpl install
function remove()
{
	// TODO need to verify if we already have installed the cms
	include_once 'core/security/security.php';
	if (!htmlget(${$start = 'start'}))
	{
		if (htmlget(${$remove = 'remove'}))
		{
			if (DBTools::isInstalled())
			{
				$dbObj = DBTools::getInstance();
				$dbObj->uninstall();
				return '<a href="?">Back</a><br />Uninstalled<br />';
			}
			header('location: ./');
			exit;
		}
		return '<a href="?remove=">Uninstall</a><br />';
	}
	return '';
}

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

//#tpl admin
function connect($args)
{
	if (!DBTools::isInstalled())
	{
		session_destroy();
		header('location: /');
		exit;
	}
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
			$prev = RequestHandler::getPrev();
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

//#tpl admin
function disconnect($args)
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

//#tpl admin
function admMenu(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	$db = DBTools::getDB(__DB__);
	$return = '';
	foreach ($db->query('select type from ' . __DB_PREFIX__ . 'contentModel as content left join ' . __DB_PREFIX__ . 'link_contentModel_lang as link on content.id=link.id_contentModel order by `order`') as $row)
		$return .= '<a href="' . $prev . 'content/' . $row['type'] . '">' . urldecode($row['type']) . '</a>';
	return $return;
}

//#tpl admin
function getContentTitle(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	return urldecode($args[3][2]);
}

//#tpl admin
function addContentLink(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	return '<a href="' . $prev . 'addContent">Add content</a><hr />';
}

//#global
function  removeRefreshBehavior(&$args)
{
	include_once 'libs/form/form.php';
	return setInputRandomSession();
}

//#tpl admin
function getSemiPrev(&$args)
{
	if (count(explode('/', RequestHandler::getPrev())) >= 5)
		return '../../';
	return './';
}

//#tpl admin
function addContent(&$args)
{
	include_once 'libs/form/form.php';
	if (!getInputRandomSession())
		return '';
	if (isset($_POST['contentTitle']) && isset($_POST['contentOrder']))
	{
		$title = urlencode($_POST['contentTitle']);
		$order = (int) $_POST['contentOrder'] * 10;
		$dbt = DBTools::getInstance();
		$dbt->insert(__DB_PREFIX__ . 'contentModel', ['order' => $order]);
		$dbt->insert(
			__DB_PREFIX__ . 'link_contentModel_lang',
			[
				'id_contentModel'	=> $dbt->lastInsertId(),
				'iso_code_lang'		=> DBTools::getMeta('defaultLanguage'),
				'type'				=> $title
			]
		);		
		header('location: ../content/' . $title);
		exit;
	}
	return '';
}

//#tpl admin
function lastFieldAdded(&$args)
{
	if (isset($_POST['addField']))
	{
		$lastFieldAdded = htmlspecialchars($_POST['addField']);
		$form  = '<form method="post" action="./">';
		$form .= '<p>Type ' . $lastFieldAdded . '</p>';
		$form .= '<label for="lastFieldAdded" title="It\'s name will be also it\'s class and it\'s id">Name : <input type="text" id="lastFieldAdded" name="lastFieldAdded" />';
		$form .= '</label>';
		$form .= '<label for="order">Order : <input type="number" id="order" name="order" />';
		$form .= '</label>';
		$form .= '<input type="hidden" name="type" value="' . $lastFieldAdded . '" />';
		$form .= '<br />';
		$form .= '<button type="submit">Add</button>';
		$form .= removeRefreshBehavior($args);
		$form .= '</form>';
		return $form;
	}
}

//#tpl admin
function insertRadio(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	if (!in_array('addRadioButton', $_POST))
		return '';
	$arrKeys = array_keys($_POST, 'addRadioButton');
	include_once 'libs/form/form.php';
	if (!getInputRandomSession())
		return '';
	foreach ($arrKeys as $key)
	{
		$row = $args[3][2];
		$field = str_replace('addRadio', '', urldecode($key));
		$option = $_POST[$key . 'Content'];
		if(!$option)
			return '';
		$db = DBTools::getDB(__DB__);
		$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
		$prepReq->bindParam(':type', $row);
		$prepReq->execute();
		$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
		if (!isset($json[$field]['options']))
			$json[$field] = array_merge($json[$field], ['options' => [0 => $option]]);
		else
			$json[$field]['options'][] = $option;
//		echo '<pre>';
//		print_r($json);
//		echo '</pre>';
		$jsonEncoded = json_encode($json);
//		echo $jsonEncoded;
		$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
		$prepReq->bindParam(':type', $row);
		$prepReq->bindParam(':inner', $jsonEncoded);
		$prepReq->execute();
	}
	return '';
}

//#tpl admin
function insertCheckbox(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	if (!in_array('addCheckboxButton', $_POST))
		return '';
	$arrKeys = array_keys($_POST, 'addCheckboxButton');
//	echo '<pre>';
//	print_r($arrKeys);
//	echo '</pre>';
	include_once 'libs/form/form.php';
	if (!getInputRandomSession())
		return '';
	foreach ($arrKeys as $key)
	{
		$row = $args[3][2];
		$field = str_replace('addCheckbox', '', urldecode($key));
		$checkbox = $_POST[$key . 'Content'];
//		echo $row . ' ' . $field . ' ' . $checkbox;
		if(!$checkbox)
			return '';
		$db = DBTools::getDB(__DB__);
		$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
		$prepReq->bindParam(':type', $row);
		$prepReq->execute();
		$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
		if (!isset($json[$field]['checkboxes']))
			$json[$field] = array_merge($json[$field], ['checkboxes' => [0 => $checkbox]]);
		else
			$json[$field]['checkboxes'][] = $checkbox;
//		echo '<pre>';
//		print_r($json);
//		echo '</pre>';
		$jsonEncoded = json_encode($json);
//		echo $jsonEncoded;
		$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
		$prepReq->bindParam(':type', $row);
		$prepReq->bindParam(':inner', $jsonEncoded);
		$prepReq->execute();
	}
	return '';
}

//#tpl admin
function insertField(&$args)
{
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	include_once 'libs/form/form.php';
	if (!htmlpost(${$lastFieldAdded = 'lastFieldAdded'}) || !htmlpost(${$type = 'type'}) || !htmlpost(${$order = 'order'}) || !$lastFieldAdded || !$order)
		return '';
	if (!getInputRandomSession())
		return '';
	$db = DBTools::getDB(__DB__);
	$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
	$prepReq->bindParam(':type', $args[3][2]);
//	echo '<pre>';
	$prepReq->execute();
//	echo '</pre>';
	$fetchResult = $prepReq->fetchAll();
	if (isset($fetchResult[0]) && isset($fetchResult[0]['inner']))
		$json = json_decode($fetchResult[0]['inner'], true);
	else
		$json = [];
//	print_r($json);
//	if (!$json)
//		$json = [];

//	echo $lastFieldAdded . ' ' . $type;

	$lastFieldAdded = preg_replace('/[^0-9a-zA-Z ]*/', '', $lastFieldAdded);
	if (!isset($json[$lastFieldAdded]))
		$json = array_merge($json, [$lastFieldAdded => ['type' => $type, 'order' => $order]]);
	else
	{
		$json[$lastFieldAdded]['type'] = $type;
		$json[$lastFieldAdded]['order'] = $order;
	}

//	print_r($json);

	$jsonEncoded = json_encode($json);
//	echo $jsonEncoded;
	$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
	$prepReq->bindParam(':type', $args[3][2]);
	$prepReq->bindParam(':inner', $jsonEncoded);
	$prepReq->execute();
	return '';
}

//#tpl admin
function listContentInner(&$args)
{
//	echo '<pre>';
//	print_r($_POST);
//	echo '</pre>';
	$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
	if (!isset($args[3][2]))
	{
		header('location: ' . $prev);
		exit;
	}
	$contentType = $args[3][2];
//	echo urldecode($contentType) . '<hr />';
	$db = DBTools::getDB(__DB__);
	$prepReq = $db->prepare("select type, `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
	$prepReq->bindParam(':type', $contentType);
	$prepReq->execute();
	$fetchResult = $prepReq->fetchAll();
//	print_r($fetchResult);
	if (empty($fetchResult))
	{
		header('location: ../../');
		exit;
	}
	if (!isset($fetchResult[0]) || !isset($fetchResult[0]['inner']))
		return '';
	$json = json_decode($fetchResult[0]['inner'], true);
	uasort($json, function ($a, $b)
	{
		return $a['order'] <=> $b['order'];
	});
//	echo '<pre>';
//	print_r($json);
//	echo '</pre>';
	$result = '<form action="./" method="post">';
	if ($json)
		foreach ($json as $key => $value)
		{
			$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm/' . urlencode($key) . '">( - )</a> ';
			if ($value['type'] == 'text' || $value['type'] == 'email' || $value['type'] == 'number' || $value['type'] == 'url' || $value['type'] == 'phone' || $value['type'] == 'date' || $value['type'] == 'color' || $value['type'] == 'file')
			{
				$result .= '<label for="' . $key . '">' . $key . ' (' . $value['order'] . ') : <br />';
				$result .= '<input class="indent" type="' . $value['type'] . '" name="' . $key . '" id="' . $key . '" />';
				$result .= '</label>';
			}
			else if ($value['type'] == 'textarea')
			{
				$result .= '<label for="' . $key . '">' . $key . ' (' . $value['order'] . ') :<br />';
				$result .= '<textarea class="indent" name="' . $key . '" id="' . $key . '"></textarea>';
				$result .= '</label>';
			}
			else if($value['type'] == 'radio')
			{
				$result .= '' . $key . '(' . $value['order'] . ')';
				$result .= '<br />';
				$result .= '<span class="indent radio">';
				if (isset($value['options']))
					foreach($value['options'] as $option)
					{
						// TODO $key => urlencode();
						$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm-opt/' . urlencode($key) . '-' . urlencode($option) . '">( - )</a> ';
						$result .= '<label for="' . urlencode($key) . $option . '">' . $option . ' : ';
						$result .= '<input type="' . $value['type'] . '" name="' . urlencode($key) . '" id="' . $key . urlencode($option) . '" />';
						$result .= '</label>';
						$result .= '<br />';
						if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm-opt' && urldecode($args[3][4]) == $key . '-' . $option)
							$result .= 'Confirmer la suppression de ' . $option . ' ? <a href="../../delete-opt/' . urlencode($key) . '-' . urlencode($option) . '">Oui</a> <a href="../../">Non</a><br />';
					}
				$result .= '<label for="addRadio' . urlencode($key) .'Content">Add option : ';
				$result .= '<input type="text" name="addRadio' . urlencode($key) .'Content" id="addRadio' . urlencode($key) .'Content" />';
				$result .= '<button type="submit" name="addRadio' . urlencode($key) . '" value="addRadioButton">Add</button>';
				$result .= '</label>';
				$result .= '</span>';
			}
			else if ($value['type'] == 'checkbox')
			{
				// TODO display correctly checkboxes
				$result .= '' . $key . '(' . $value['order'] . ')';
				$result .= '<br />';
				$result .= '<span class="indent checkbox">';
				if (isset($value['checkboxes']))
					foreach($value['checkboxes'] as $checkbox)
					{
						// TODO $key => urlencode();
						$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm-chk/' . urlencode($key) . '-' . urlencode($checkbox) . '">( - )</a> ';
						$result .= '<label for="' . urlencode($key) . '-' .  urlencode($checkbox) . '">' . $checkbox . ' : ';
						$result .= '<input type="' . $value['type'] . '" name="' . urlencode($key) . '-' . urlencode($checkbox) . '" id="' . urlencode($key) . '-' .  urlencode($checkbox) . '" />';
						$result .= '</label>';
						$result .= '<br />';
						if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm-chk' && urldecode($args[3][4]) == $key . '-' . $checkbox)
							$result .= 'Confirmer la suppression de ' . $checkbox . ' ? <a href="../../delete-chk/' . urlencode($key) . '-' . urlencode($checkbox) . '">Oui</a> <a href="../../">Non</a><br />';
					}
				$result .= '<label for="addCheckbox' . urlencode($key) .'Content">Add checkbox : ';
				$result .= '<input type="text" name="addCheckbox' . urlencode($key) .'Content" id="addCheckbox' . urlencode($key) .'Content" />';
				$result .= '<button type="submit" name="addCheckbox' . urlencode($key) . '" value="addCheckboxButton">Add</button>';
				$result .= '</label>';
				$result .= '</span>';
			}
			else if ($value['type'] == 'file')
			{
				$result .= '';
			}
			else
			{
				$result .= 'toto';
			}
			$result .= '<br />';
			if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm' && urldecode($args[3][4]) == $key)
					$result .= 'Confirmer la suppression de ' . $key . ' ? <a href="../../delete/' . urlencode($key) . '">Oui</a> <a href="../../">Non</a><br />';
		}
	$result .= setInputRandomSession();
	$result .= '<button type="submit" name="save">Save</button>';
	$result .= '</form>';
	return $result;
}

//#tpl admin
function deleteOption(&$args)
{
	if (!isset($args[3][2]))
		return '';
	if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete-opt')
		return '';
	$db = DBTools::getDB(__DB__);
	$type = urldecode($args[3][2]);
	$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel where type=:type");
	$prepReq->bindParam(':type', $type);
	$prepReq->execute();
	$json = json_decode($prepReq->fetchAll()[0]['inner'], true);

//	echo urldecode($args[3][4]);
	$arrRowOption = explode('-', urldecode($args[3][4]));
//	echo '<pre>';
//	print_r($json);
//	echo '</pre>';
	
	if (isset($json[$arrRowOption[0]]))
		if (($test = array_search($arrRowOption[1], $json[$arrRowOption[0]]['options'])) !== false)
			unset($json[$arrRowOption[0]]['options'][$test]);
	
	$jsonEncoded = json_encode($json);
//	echo $jsonEncoded;
	$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel SET `inner` = :inner WHERE type = :type");
	$prepReq->bindParam(':type', $type);
	$prepReq->bindParam(':inner', $jsonEncoded);
	$prepReq->execute();
	header('location: ../../');
	return '';
}

//#tpl admin
function deleteCheckbox(&$args)
{
	if (!isset($args[3][2]))
		return '';
	if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete-chk')
		return '';
	$db = DBTools::getDB(__DB__);
	$type = urldecode($args[3][2]);
	$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel where type=:type");
	$prepReq->bindParam(':type', $type);
	$prepReq->execute();
	$json = json_decode($prepReq->fetchAll()[0]['inner'], true);

//	echo urldecode($args[3][4]);
	$arrRowCheckbox = explode('-', urldecode($args[3][4]));
//	echo '<pre>';
//	print_r($json);
//	echo '</pre>';
	
	if (isset($json[$arrRowCheckbox[0]]))
		if (($test = array_search($arrRowCheckbox[1], $json[$arrRowCheckbox[0]]['checkboxes'])) !== false)
			unset($json[$arrRowCheckbox[0]]['checkboxes'][$test]);
	
	$jsonEncoded = json_encode($json);
//	echo $jsonEncoded;
	$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel SET `inner` = :inner WHERE type = :type");
	$prepReq->bindParam(':type', $type);
	$prepReq->bindParam(':inner', $jsonEncoded);
	$prepReq->execute();
	header('location: ../../');
	return '';
}

//#tpl admin
function deleteField(&$args)
{
	if (!isset($args[3][2]))
		return '';
	if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete')
		return '';
	$db = DBTools::getDB(__DB__);
	$type = urldecode($args[3][2]);
	$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
	$prepReq->bindParam(':type', $type);
	$prepReq->execute();
	$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
	if (isset($json[urldecode($args[3][4])]))
		unset($json[urldecode($args[3][4])]);
//		print_r($json);
	$jsonEncoded = json_encode($json);
//	echo $jsonEncoded;
	$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
	$prepReq->bindParam(':type', $type);
	$prepReq->bindParam(':inner', $jsonEncoded);
	$prepReq->execute();
	header('location: ../../');
	return '';
}

?>
