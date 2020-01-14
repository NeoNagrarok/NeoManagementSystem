<?php

//include 'libs/superVariables/server.php';

function createFile($filePath, $content)
{
	$file = fopen($filePath, "a+");
	fputs($file, $content);
	fclose($file);
}

function recreateFile($filePath, $content)
{
	eraseFile($filePath);
	createFile($filePath, $content);	
}

function getContentFile($filePath)
{
	$contentFile = '';
	if (file_exists($filePath)) // Add security in case where $filepath is a directory and not a file !
	{
		$file = fopen($filePath, "r");
		while (!feof($file))
		{
			$contentFile .= fgets($file);
		}
		fclose($file);
	}
	else
		return '<div>File doesn\'t exist.</div>';
	return $contentFile;
}

function getContentDir($dirPath)
{
	$numberDir = 0;
	$tabDir = NULL;
	if ($dir = opendir($dirPath))
	{
		while(false !== ($file = readdir($dir)))
		{
			if ($file != '.' && $file != '..' && $file != 'index.php'/* && $file[0] != '.'*/)
			{
				$tabDir[$numberDir] = $file;
				$numberDir++;
			}
		}
		//echo 'Il y a ' . $numberDir . ' dossiers et fichiers<br />';
		closedir($dir);
	}
	else
	{
		echo 'Le dossier n\'a pas pu être ouvert<br />';
	}
	return $tabDir;
}

function createFolder($path)
{
	mkdir($path, 0777, true);
}

function copyAll($path, $dest)
{
	$tab = explode('/', $path);
	$dest .= '/' . end($tab);
	$dest = preg_replace('#//#is', '/', $dest);
	//echo $path . ' ' . $dest . '<hr />';
	if (is_dir($path))
	{
		createFolder($dest);
		$contentDir = getContentDir($path);
		if (isset($contentDir))
		{
			foreach ($contentDir as $element)
			{
				$newPath = $path . '/' . $element;
				$newPath = preg_replace('#//#is', '/', $newPath);
				copyAll($newPath, $dest);
			}
		}
	}
	else
	{
		copy($path, $dest);
	}
}

function eraseFile($path)
{
	if (file_exists($path))
	{
		unlink($path);
		return true;
	}
	return false;
}

function eraseFolder($path)
{
	if (file_exists($path))
	{
		//echo '<br /><br />' . $path . '<br /><br />';
		rmdir($path);
		return true;
	}
	return false;
}

function eraseAll($path)
{
	if (is_dir($path))
	{
		$tabDir = getContentDir($path);
		if (count($tabDir) > 0)
		{
			foreach ($tabDir as $element)
			{
				eraseAll($path . '/' . $element);
			}
		}
		eraseFolder($path);
	}
	else
	{
		eraseFile($path);
	}
}

function countContent($path)
{
	if (is_dir($path))
	{
		$tabDir = getContentDir($path);
		return count($tabDir);
	}
}

function getAuthorization($ressource)
{
	$tabResult[0] = false;
	$tabResult[1] = 'La ressource n\'est pas disponible !<br />';
	$authPath = 'admin/authFile.php';
	if (!file_exists($authPath))
	{
		createFile($authPath, '');
	}
	$authContent = getContentFile($authPath);
	preg_match('#.*<([0-9]*)><' . $ressource . '><(.*)>.*#Uis', $authContent, $authResult);
	if (count($authResult) == 0)
	{
		$tabResult[0] = true;
		$tabResult[1] = 'On peut bloquer la ressource !<br />';
		$tabResult[2] = 'putOff';
		// La ressource n'existe pas dans le fichier
	}
	else
	{
		if (getIp() != $authResult[2])
		{
			if ($authResult[1] > time() + 24 * 60 * 60)
			{
				$tabResult[0] = true;
				$tabResult[1] = 'On peut prendre le contrôle de la ressource !<br />';
				$tabResult[2] = 'turnOff';
				// La ressource existe, a été bloquée mais le délai est dépassé
			}
			return $tabResult;
		}
		$tabResult[0] = true;
		$tabResult[1] = 'On peut appliquer les modifications à la ressource !<br />';
		$tabResult[2] = 'continue';
		// La ressource existe, on en a toujours le contrôle
	}
	return $tabResult;
	// Si on est passé par aucun des if, la ressource existe, on n'a pas les droit dessus et le délai n'a pas expiré
}

function putAuthorizationOn($ressource) // Vous rendez disponible la ressource
{
	// Retire la ressource de la liste
	$authContent = getContentFile('admin/authFile.php');
	$result = preg_replace('#<[0-9]*><' . $ressource . '><.*>#Uis', '', $authContent);
	recreateFile('admin/authFile.php', $result);
}

function putAuthorizationOff($ressource) // Vous rendez indisponible la ressource
{
	$tab = getAuthorization($ressource);
	if ($tab[0] && $tab[2] != 'continue')
	{
		$authContent = getContentFile('admin/authFile.php');
		if ($tab[2] == 'putOff')
		{
			$newContent = '<' . time() . '><' . $ressource . '><' . getIp() . '>' . $authContent;
		}
		elseif ($tab[2] == 'turnOff')
		{
			$newContent = preg_replace('#<[0-9]*><' . $ressource . '><.*>#Uis', '<' . time() . '><' . $ressource . '><' . getIp() . '>', $authContent);
		}
		// On recréer le fichier d'autorisation avec le bon contenu
		recreateFile('admin/authFile.php', $newContent);
		$_SESSION['authSession'] = $ressource;
	}
	if (!$tab[0])
	{
		return false;
	}
	return true;
}

?>
