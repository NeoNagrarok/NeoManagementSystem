<?php

abstract class ModuleGlobalController extends controller
{
	protected function __construct()
	{
		/* Put some method call here which are already in function.php */
	}
	
	static public function getModule($hook, &$mod)
	{
		if(!DBTools::isInstalled())
			return [];
		$mod = DBTools::getDB()->query('select moduleName from ' . __DB_PREFIX__ . 'moduleHook where hookName="' . $hook . '"')->fetch();
		/* TODO Warning !!! get the only first occurence of this :/ USE $mod to make an array !!! */
		return $mod;
	}
	
	static public function isInstalled()
	{
		return DBTools::getDB()->query('select count(moduleName) from ' . __DB_PREFIX__ . 'moduleHook where moduleName="' . static::class . '"')->fetch()[0] > 0;
	}
	
	public function install()
	{
		if (self::isInstalled())
			return false;
		$hooks = array_filter(get_class_methods(static::class), function($e){
				return strpos($e, 'hook_') === 0;
			});
		foreach ($hooks as $hook)
			DBTools::getInstance()->insert(__DB_PREFIX__ . 'moduleHook', ['hookName' => $hook, 'moduleName' => static::class]);
		return true;
	}
	
	public function uninstall()
	{
		if (!self::isInstalled())
			return false;
		DBTools::getDB()->exec('delete from ' . __DB_PREFIX__ . 'moduleHook where moduleName="' . static::class . '"');
		//DBTools::dropTable(static::class);
		return true;
	}
	abstract public function getConfig();
	
	/* add some hook functions from any child class ;) */
}

?>
