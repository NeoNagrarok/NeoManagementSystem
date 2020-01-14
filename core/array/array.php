<?php
	function pushAtEnd(&$tab, $key)
	{
		if (isset($tab[$key]))
		{
			$tmp = $tab[$key];
			unset($tab[$key]);
			$tab[$key] = $tmp;
		}
		else
		{
			foreach ($tab as $keyTab => $element)
			{
				if (substr($keyTab, 0, strlen($key)) == $key)
				{
					pushAtEnd($tab, $keyTab);
				}
			}
		}
	}
?>
