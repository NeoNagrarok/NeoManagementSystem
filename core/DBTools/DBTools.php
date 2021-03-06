<?php

/* TODO use right database / tables (etc.) names from prefixing in configuration file */

class DBTools
{
	private function __construct()
	{
		try
		{
			$this->db = new PDO('mysql:host=' . __DB_HOST__ . ';charset=utf8', __DB_USER__, __DB_PASSWORD__);
			if (__DEBUG__)
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch (PDOException $e)
		{
			/* TODO try catch arround the page setting ? in order to set $page to error (with error context ?) anf handle the error thanks to it */
			echo '<pre>';
			echo '<h2>getMessage</h2>' . $e->getMessage() . '<hr />';
			echo '<h2>getPrevious</h2>' . $e->getPrevious() . '<hr />';
			echo '<h2>getCode</h2>' . $e->getCode() . '<hr />';
			echo '<h2>getFile</h2>' . $e->getFile() . '<hr />';
			echo '<h2>getLine</h2>' . $e->getLine() . '<hr />';
			
			echo '<h2>getTrace</h2>' . $e->getTrace() . '<hr />';
			echo '<h2>getTraceAsString</h2>' . $e->getTraceAsString() . '<hr />';
			echo '</pre>';
			die(); // ???
		}
	}

	public static function getInstance()
	{
		if(is_null(self::$singleton))
			self::$singleton = new DBTools();  
		return self::$singleton;
	}

	public static function getDB($database = null)
	{
		if(is_null(self::$singleton))
			self::$singleton = new DBTools();
		if ($database)
			self::$singleton->db->query('use ' . $database . '');
		return self::$singleton->db;
	}

	public function install()
	{
		try
		{
			$sql = "CREATE DATABASE IF NOT EXISTS " . __DB__;
			self::$singleton->db->exec($sql);
			self::$singleton->db->query("use " . __DB__);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'chmod` (
				`code` varchar(3) not null,
				`read` tinyint(1),
				`write` tinyint(1),
				`execute` tinyint(1),
				PRIMARY KEY (`code`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'meta` (
				`id` int unsigned auto_increment not null,
				`key` varchar(50),
				`value` varchar(50),
				PRIMARY KEY (id)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'lang` (
				`iso_code` varchar(2),
				`name` varchar(45),
				`active` tinyint(1),
				PRIMARY KEY (`iso_code`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'contentModel` (
				`id` int unsigned auto_increment not null,
				`inner` json,
				`order` int unsigned,
				PRIMARY KEY (`id`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'link_contentModel_lang` (
				`id_contentModel` int unsigned not null,
				`iso_code_lang` varchar(2) not null,
				`type` varchar(20) NOT NULL,
				FOREIGN KEY (id_contentModel) REFERENCES ' . __DB_PREFIX__ . 'contentModel (id),
				FOREIGN KEY (iso_code_lang) REFERENCES ' . __DB_PREFIX__ . 'lang (iso_code)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'content` (
				`id` int unsigned auto_increment not null,
				`id_contentModel` int unsigned not null,
				`code_chmod` varchar(3) not null,
				`date` date,
				`formData` json,
				PRIMARY KEY (`id`),
				FOREIGN KEY (id_contentModel) REFERENCES ' . __DB_PREFIX__ . 'contentModel (id),
				FOREIGN KEY (code_chmod) REFERENCES ' . __DB_PREFIX__ . 'chmod (code)
			)';
			self::$singleton->db->exec($sql);

			$sql = 'CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'moduleHook` (
			`hookName` varchar(40) not null,
			`moduleName` varchar(40) not null
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'userGroup` (
				`id` int unsigned auto_increment not null,
				`taxonomy` varchar(20),
				PRIMARY KEY (`id`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'user` (
				`id` int unsigned auto_increment not null,
				`pseudo` varchar(50),
				`email` varchar(64),
				`password` varchar(50),
				`id_group` int unsigned not null,
				PRIMARY KEY (`id`),
				FOREIGN KEY (id_group) REFERENCES ' . __DB_PREFIX__ . 'userGroup (id)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'link_content_chmod_user` (
				`id_content` int unsigned not null,
				`code_chmod` varchar(3),
				`id_user` int unsigned not null,
				FOREIGN KEY (id_content) REFERENCES ' . __DB_PREFIX__ . 'content (id),
				FOREIGN KEY (code_chmod) REFERENCES ' . __DB_PREFIX__ . 'chmod (code),
				FOREIGN KEY (id_user) REFERENCES ' . __DB_PREFIX__ . 'user (id)
			)';
			self::$singleton->db->exec($sql);

			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . 'link_content_chmod_userGroup` (
				`id_content` int unsigned not null,
				`code_chmod` varchar(3),
				`id_userGroup` int unsigned not null,
				FOREIGN KEY (id_content) REFERENCES ' . __DB_PREFIX__ . 'content (id),
				FOREIGN KEY (code_chmod) REFERENCES ' . __DB_PREFIX__ . 'chmod (code),
				FOREIGN KEY (id_userGroup) REFERENCES ' . __DB_PREFIX__ . 'userGroup (id)
			)';
			self::$singleton->db->exec($sql);

//			$sql = 'CREATE TABLE IF NOT EXISTS `'.__DB_PREFIX__.'` (
//			)';
//			self::$singleton->db->exec($sql);
			
			return true;
		}
		catch (Exception $e)
		{
				die('Erreur : ' . $e->getMessage());
		}
		return false;
	}
	
	public function uninstall()
	{
		return self::getDB()->query('DROP DATABASE IF EXISTS ' . __DB__);
	}
	
	public static function isInstalled()
	{
		return self::getDB()->query('SHOW DATABASES LIKE "' . __DB__ . '"')->fetch();
	}
	
	public function defaultLanguageSetting()
	{
		/* TODO find a way to execute this code only once time */
		/* TODO set from a config file ? or contractual form from installation process ? */
		$prepReq = self::$singleton->db->prepare("INSERT INTO " . __DB_PREFIX__ . "lang (`name`, `active`, `iso_code`) VALUES (:name, true, :iso_code)");
		$name = 'français';
		$prepReq->bindParam(':name', $name);
		$iso_code = 'FR';
		$prepReq->bindParam(':iso_code', $iso_code);
		$prepReq->execute();
		self::setMeta('defaultLanguage', 'FR');
	}
	
	public function defaultTheme()
	{
		self::setMeta('theme', 'default');
	}
	
	public function installDone()
	{
		return self::setMeta('installed', 'true');
	}
	
	public function insert($table, $pairs)
	{
		$fields = '';
		$values = '';
		foreach ($pairs as $key => $value)
		{
			$fields .= '`' . $key . '`,';
			$values .= ':' . $key . ',';
		}
		$fields = substr($fields, 0, -1);
		$values = substr($values, 0, -1);
		$prepReq = DBTools::getDB(__DB__)->prepare('insert into ' . $table . ' (' . $fields . ') values (' . $values . ')');
		$prepReq->execute($pairs);

	}
	
	public function lastInsertId()
	{
		return $this->db->lastInsertId();
	}
	
	// TODO study the utility for getMeta paired with insert method ... ?
	
	public static function getMeta($key)
	{
		$db = self::getDB(__DB__);
		$prepReq = $db->prepare('select value from ' . __DB_PREFIX__ . 'meta where `key`=:key');
		$prepReq->bindParam(':key', $key);
		$prepReq->execute();
		$result = $prepReq->fetchAll();
		if ($result)
			return $result[0]['value'];
		return null;
	}
	
	public static function setMeta($key, $value)
	{
		$db = self::getDB(__DB__);
		$prepReq = $db->prepare('insert into ' . __DB_PREFIX__ . 'meta (`key`, value) values (:key, :value)');
		$prepReq->bindParam(':value', $value);
		$prepReq->bindParam(':key', $key);
		return $prepReq->execute();
	}
	
	public static function tableExists($table)
	{
		// Try a select statement against the table
		// Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
		try
		{
		    $result = self::$singleton->db->prepare('SELECT 1 FROM ' . __DB_PREFIX__ . $table . ' LIMIT 1')->execute();
		}
		catch (Exception $e)
		{
		    // We got an exception == table not found
		    return false;
		}
		// Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
		return $result !== false;
	}
	
	// TODO with list of field it will be better ... => $fieldList
	public static function createTable($table, $fieldList = [], $constraints = '')
	{
		try
		{
			$fieldString = '';
			foreach ($fieldList as $field => $description)
				$fieldString .= '`' . $field . '` ' . $description . ',';
			$fieldString = preg_replace('/,$/', '', $fieldString);
			$sql = 'CREATE TABLE IF NOT EXISTS `' . __DB_PREFIX__ . $table . '` (
					' . $fieldString . '
					' . $constraints . '
				)';
//			echo $sql . '<br />';
		    $result = self::$singleton->db->exec($sql);
//		    echo $result;
		}
		catch (Exception $e)
		{
			return false;
		}
		return $result !== false;
	}

	public static function dropTable($table)
	{
		try
		{
		    $result = self::$singleton->db->exec('DROP TABLE IF EXISTS ' . __DB_PREFIX__ . $table);
		}
		catch (Exception $e)
		{
			return false;
		}
		return $result !== false;
	}

	private static $singleton = null;
	private $db;
}

?>
