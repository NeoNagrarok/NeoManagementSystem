<?php

class cart extends ModuleGlobalController
{
	public function install()
	{
		echo 'install';
	}
	
	public function uninstall()
	{
		echo 'uninstall';
	}
	
	public function getConfig()
	{
		echo 'getConfig';
		return 'getConfig';
	}
}

?>
