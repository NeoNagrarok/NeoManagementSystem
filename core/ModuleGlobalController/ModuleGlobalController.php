<?php

abstract class ModuleGlobalController extends controller
{
	protected function __construct()
	{
		/* Put some method call here which are already in function.php */
	}
	
	abstract public function install();
	abstract public function uninstall();
	abstract public function getConfig();
	
	/* add some hook functions from any child class ;) */
}

?>
