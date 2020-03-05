<?php
	const MAXLENGTHONE = 20;
	const MAXLENGTHTWO = 40;
	const MAXLENGTHTHREE = 255;

	function my_session_start()
	{
		session_start();
	}
	
	function htmlget(&$var) // htmlget(${$var = 'var'}); where var is the strinf in $_GET['var'];
	{
		if (isset($_GET[$var]))
		{
			$var = htmlentities($_GET[$var], ENT_QUOTES);
			return true;
		}
		return false;
	}
	
	function htmlpost(&$var)
	{
		if (isset($_POST[$var]))
		{
			$var = htmlentities($_POST[$var], ENT_QUOTES);
			return true;
		}
		return false;
	}

	function htmlsession(&$var)
	{
		if (isset($_SESSION[$var]))
		{
			$var = htmlentities($_SESSION[$var], ENT_QUOTES);
			return true;
		}
		return false;
	}

	function hashMdp($saltOne, $mdp, $saltTwo)
	{
		$saltOne = $saltTwo . $saltOne;
		$saltTwo = $saltOne . $saltTwo;
		$sha1 = sha1($saltOne.$mdp.$saltTwo);
		return $sha1;
	}
	
	function unHashMdp($mdp)
	{
		if (hashMdp($result[3], $mdp, $result[4]) == $result[1])
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function captchat()
	{
		$tableMult['un']['un'] = 1;
		$tableMult['un']['deux'] = 2;
		$tableMult['un']['trois'] = 3;
		$tableMult['un']['quatre'] = 4;
		$tableMult['un']['cinq'] = 5;
		$tableMult['un']['six'] = 6;
		$tableMult['un']['sept'] = 7;
		$tableMult['un']['huit'] = 8;
		$tableMult['un']['neuf'] = 9;
		$tableMult['un']['dix'] = 10;
		
		$tableMult['deux']['un'] = 2;
		$tableMult['deux']['deux'] = 4;
		$tableMult['deux']['trois'] = 6;
		$tableMult['deux']['quatre'] = 8;
		$tableMult['deux']['cinq'] = 10;
		$tableMult['deux']['six'] = 12;
		$tableMult['deux']['sept'] = 14;
		$tableMult['deux']['huit'] = 16;
		$tableMult['deux']['neuf'] = 18;
		$tableMult['deux']['dix'] = 20;
		
		$tableMult['trois']['un'] = 3;
		$tableMult['trois']['deux'] = 6;
		$tableMult['trois']['trois'] = 9;
		$tableMult['trois']['quatre'] = 12;
		$tableMult['trois']['cinq'] = 15;
		$tableMult['trois']['six'] = 18;
		$tableMult['trois']['sept'] = 21;
		$tableMult['trois']['huit'] = 24;
		$tableMult['trois']['neuf'] = 27;
		$tableMult['trois']['dix'] = 30;
		
		$tableMult['quatre']['un'] = 4;
		$tableMult['quatre']['deux'] = 8;
		$tableMult['quatre']['trois'] = 12;
		$tableMult['quatre']['quatre'] = 16;
		$tableMult['quatre']['cinq'] = 20;
		$tableMult['quatre']['six'] = 24;
		$tableMult['quatre']['sept'] = 28;
		$tableMult['quatre']['huit'] = 32;
		$tableMult['quatre']['neuf'] = 36;
		$tableMult['quatre']['dix'] = 40;
		
		$tableMult['cinq']['un'] = 5;
		$tableMult['cinq']['deux'] = 10;
		$tableMult['cinq']['trois'] = 15;
		$tableMult['cinq']['quatre'] = 20;
		$tableMult['cinq']['cinq'] = 25;
		$tableMult['cinq']['six'] = 30;
		$tableMult['cinq']['sept'] = 35;
		$tableMult['cinq']['huit'] = 40;
		$tableMult['cinq']['neuf'] = 45;
		$tableMult['cinq']['dix'] = 50;
		
		$tableMult['six']['un'] = 6;
		$tableMult['six']['deux'] = 12;
		$tableMult['six']['trois'] = 18;
		$tableMult['six']['quatre'] = 24;
		$tableMult['six']['cinq'] = 30;
		$tableMult['six']['six'] = 36;
		$tableMult['six']['sept'] = 42;
		$tableMult['six']['huit'] = 48;
		$tableMult['six']['neuf'] = 54;
		$tableMult['six']['dix'] = 60;
		
		$tableMult['sept']['un'] = 7;
		$tableMult['sept']['deux'] = 14;
		$tableMult['sept']['trois'] = 21;
		$tableMult['sept']['quatre'] = 28;
		$tableMult['sept']['cinq'] = 35;
		$tableMult['sept']['six'] = 42;
		$tableMult['sept']['sept'] = 49;
		$tableMult['sept']['huit'] = 56;
		$tableMult['sept']['neuf'] = 63;
		$tableMult['sept']['dix'] = 70;
		
		$tableMult['huit']['un'] = 8;
		$tableMult['huit']['deux'] = 16;
		$tableMult['huit']['trois'] = 24;
		$tableMult['huit']['quatre'] = 32;
		$tableMult['huit']['cinq'] = 40;
		$tableMult['huit']['six'] = 48;
		$tableMult['huit']['sept'] = 56;
		$tableMult['huit']['huit'] = 64;
		$tableMult['huit']['neuf'] = 72;
		$tableMult['huit']['dix'] = 80;
		
		$tableMult['neuf']['un'] = 9;
		$tableMult['neuf']['deux'] = 18;
		$tableMult['neuf']['trois'] = 27;
		$tableMult['neuf']['quatre'] = 36;
		$tableMult['neuf']['cinq'] = 45;
		$tableMult['neuf']['six'] = 54;
		$tableMult['neuf']['sept'] = 63;
		$tableMult['neuf']['huit'] = 72;
		$tableMult['neuf']['neuf'] = 81;
		$tableMult['neuf']['dix'] = 90;
		
		$tableMult['dix']['un'] = 10;
		$tableMult['dix']['deux'] = 20;
		$tableMult['dix']['trois'] = 30;
		$tableMult['dix']['quatre'] = 40;
		$tableMult['dix']['cinq'] = 50;
		$tableMult['dix']['six'] = 60;
		$tableMult['dix']['sept'] = 70;
		$tableMult['dix']['huit'] = 80;
		$tableMult['dix']['neuf'] = 90;
		$tableMult['dix']['dix'] = 100;
		
		$tableNumbers[0] = 'un';
		$tableNumbers[1] = 'deux';
		$tableNumbers[2] = 'trois';
		$tableNumbers[3] = 'quatre';
		$tableNumbers[4] = 'cinq';
		$tableNumbers[5] = 'six';
		$tableNumbers[6] = 'sept';
		$tableNumbers[7] = 'huit';
		$tableNumbers[8] = 'neuf';
		$tableNumbers[9] = 'dix';
		
		$n1 = $tableNumbers[mt_rand(0, 9)];
		$n2 = $tableNumbers[mt_rand(0, 9)];
		
		$phrase = '<label for="captchat">Vérification anti-robot : Combien font <b>' . $n1 . '</b> multiplié par <b>' . $n2 . '</b> ?</label>';
		$_SESSION['captchatResult'] = $tableMult[$n1][$n2];
		
		$phrase .= '<br /><input type="number" name="captchat" id="captchat" placeholder="0 à 100" title="Entrez le résultat qui vous est demandé en chiffre" required /><span>(Le résultat est à écrire en chiffre)<br /><br /></span>';
		return $phrase;
	}
	
	function verifCaptchat()
	{
		if (isset($_POST['captchat']))
		{
			if (isset($_SESSION['captchatResult']))
			{
				if (htmlentities($_SESSION['captchatResult'], ENT_QUOTES) == htmlentities($_POST['captchat'], ENT_QUOTES))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	function verifLen($str, $nbr)
	{
		if (strlen($str) > $nbr)
		{
			return false;
		}
		return true;
	
	}
	
	function isClearString($str)
	{
		$strBis = preg_replace('#[^a-zA-Z0-9]*#is', '', $str);
		if ($str != $strBis)
		{
			return false;
		}
		return true;
	}
?>
