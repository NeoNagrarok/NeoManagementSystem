<?php

class cart extends ModuleGlobalController
{
	public function install()
	{
		if (parent::install())
		{
			DBTools::createTable(static::class, [
					'id_user'		=>	'int unsigned not null',
					'id_product'	=>	'int unsigned not null',
					'quantity'		=>	'int unsigned not null'
				],
				',
				FOREIGN KEY (id_user) REFERENCES ' . __DB_PREFIX__ . 'user (id),
				FOREIGN KEY (id_product) REFERENCES ' . __DB_PREFIX__ . 'content (id)
				'
				);
			header('location: ./');
		}
	}
	
	public function uninstall()
	{
		if (parent::uninstall())
		{
			DBTools::dropTable(static::class);
			echo 'uninstalling ' . static::class;
			header('location: ./');
		}
	}
	
	public function getConfig()
	{
		if (!DBTools::tableExists(__CLASS__))
			return '';
		return 'getConfig';
	}
	
	public function hook_getConfigureCart(&$args)
	{
		$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
		return '<a href="./' . $prev . 'modules/' . static::class . '">Cart</a>';
	}
}

?>
