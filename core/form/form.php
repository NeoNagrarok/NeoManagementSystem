<?php

include_once 'core/security/security.php';

/*
if (!isset($_SESSION['randomSession']))
	$_SESSION['randomSession'] = uniqid(); // Plus besoin de redirection
			
if (htmlsession(${$randomSession = 'randomSession'}))
					$display .= '<input type="hidden" name="randomPost" value="' . $randomSession . '" />';
			
*/

	function setInputRandomSession()
	{
		/*if (htmlsession(${$randomSession = 'randomSession'}))
			unset($_SESSION['randomSession']);*/
		$_SESSION['randomSession'] = uniqid();
		return '<input type="hidden" name="randomPost" value="' . $_SESSION['randomSession'] . '" />';
	}
	
	function getInputRandomSession()
	{
		$return = false;
		if (htmlsession(${$randomSession = 'randomSession'}) && htmlpost(${$randomPost = 'randomPost'}))
			if ($randomSession == $randomPost)
			{
				$return = true;
				unset($_SESSION['randomSession']);
			}
		return $return;
	}

?>
