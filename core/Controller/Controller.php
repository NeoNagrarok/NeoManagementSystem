<?php

	abstract class Controller
	{
		protected function __construct()
		{
			/* Put some method call here which are already in function.php */
		}
		
		public static function getInstance($class)
		{
			$class = static::class;
			if(is_null(self::$singleton) || !isset(self::$singleton[$class]))
				self::$singleton[$class] = new $class();  
			return self::$singleton[$class];
		}
		
		public function get($tpl)
		{
			$templateReader = TemplateReader::getInstance();
			$contentTpl = getContentFile($tpl);
			return $templateReader->parser($contentTpl);	
		}
		
		public function caller($args)
		{
//			print_r($this->callList);
			foreach ($this->callList as $func)
			{
//				echo $func;
				static::$func($args);
			}
		}
		
		protected static $singleton = null;
		protected $callList = [];
	}

?>
