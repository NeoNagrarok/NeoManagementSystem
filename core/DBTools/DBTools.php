<?php

/* TODO use right database / tables (etc.) names from prefixing in configuration file */

class DBTools
{
	private function __construct()
	{
		$this->db = new PDO('mysql:host=192.168.1.30;charset=utf8', 'root', 'dadfba16');
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
			$sql = "CREATE DATABASE IF NOT EXISTS nms";
			self::$singleton->db->exec($sql);
			self::$singleton->db->query("use nms");
			
			$sql = 'CREATE TABLE IF NOT EXISTS `meta` (
				`id` int unsigned auto_increment not null,
				`key` varchar(50),
				`value` varchar(50),
				PRIMARY KEY (id)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `lang` (
				`iso_code` varchar(2),
				`name` varchar(45),
				`active` tinyint(1),
				PRIMARY KEY (`iso_code`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `contentModel` (
				`id` int unsigned auto_increment not null,
				`inner` json,
				`order` int unsigned,
				PRIMARY KEY (`id`)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `link_contentModel_lang` (
				`id_contentModel` int unsigned not null,
				`iso_code_lang` varchar(2) not null,
				`type` varchar(20) NOT NULL,
				FOREIGN KEY (id_contentModel) REFERENCES contentModel (id),
				FOREIGN KEY (iso_code_lang) REFERENCES lang (iso_code)
			)';
			self::$singleton->db->exec($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS `content` (
				`id` int unsigned auto_increment not null,
				`id_contentModel` int unsigned not null,
				`code_chmod` varchar(3) not null,
				`title` varchar(50),
				`slug` varchar(20),
				`date` date,
				`formData` json,
				PRIMARY KEY (`id`),
				FOREIGN KEY (id_contentModel) REFERENCES contentModel (id)
			)';
			self::$singleton->db->exec($sql);
			
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
		return self::getDB()->query('DROP DATABASE IF EXISTS nms');
	}
	
	public static function isInstalled()
	{
		return self::getDB()->query('SHOW DATABASES LIKE "nms"')->fetch();
	}
	
	public function defaultLanguageSetting()
	{
		/* TODO find a way to execute this code only once time */
		/* TODO set from a config file ? or contractual form from installation process ? */
		$prepReq = self::$singleton->db->prepare("INSERT INTO lang (`name`, `active`, `iso_code`) VALUES (:name, true, :iso_code)");
		$name = 'français';
		$prepReq->bindParam(':name', $name);
		$iso_code = 'FR';
		$prepReq->bindParam(':iso_code', $iso_code);
		$prepReq->execute();
		self::setMeta('defaultLanguage', 'FR');
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
		$prepReq = $db->prepare('select value from meta where `key`=:key');
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
		$prepReq = $db->prepare('insert into meta (`key`, value) values (:key, :value)');
		$prepReq->bindParam(':value', $value);
		$prepReq->bindParam(':key', $key);
		return $prepReq->execute();
	}
	
	private static $singleton = null;
	private $db;
}

?>
