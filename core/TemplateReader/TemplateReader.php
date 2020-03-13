<?php
	include_once 'core/files/files.php';

	class TemplateReader
	{
		private function __construct()
		{
//			echo 'TemplateReaderConstruct<br />';
			$this->listIf['__first__'] = 'true';
			
			$this->context = 'themes';
			$this->theme = DBTools::getMeta('theme');
			$this->page = 'home';
			/*
			*	[routeVar] = 'folder where to search index.tpl'
			*	example : ['admin'] = 'admin' need a admin folder in root folder.
			*/
			$this->controllers['admin'] = 'get';
			$this->controllers['addContent'] = 'get';
			$this->controllers['content'] = 'get';
			
			$this->contexts['admin'] = 'admin';
			$this->contexts['install'] = 'install';
			/* TODO get the good list of pages from where data are stored !!! */
			/* NOTE may be a view or controller ! */
			$this->pages['themes']['home'] = 'tpl';
			$this->pages['themes']['adminTest'] = 'tpl';
			$this->pages['themes']['aaa'] = 'tpl';
			$this->pages['themes']['bbb'] = 'tpl';
			
			$this->pages['admin']['home'] = 'tpl';
			$this->pages['admin']['addContent'] = 'tpl';
			$this->pages['admin']['content'] = 'tpl';
			
			$this->pages['install']['home'] = 'tpl';
		}
		
		public static function getInstance()
		{
			if(is_null(self::$singleton))
				self::$singleton = new TemplateReader();  
			return self::$singleton;
		}

		public static function getTabVar()
		{
			return self::$tabVar;
		}
		
		public function getContext()
		{
			return $this->context;
		}
		
		public function getTheme()
		{
			return $this->theme;
		}
		
		public function setTabVar($index, $value = null)
		{
			if (!$value)
				$value = $this->getHTML();
			self::$tabVar[$index] = $value;
		}
		
		public function getHTML($tpl)
		{
			$explode = explode('/', $tpl);
			$controllerName = explode('.', end($explode))[0];
			if (isset($this->controllers[$controllerName]))
			{
				$args[0] = $this->context . '/' . $this->theme;
				$args[0] = isset($func[1]) ? $func[1] . '/' . $this->theme : $args[0];
				$args[1] = &$this->listIf;
				$args[2] = &$this;
				$args[3] = &self::$tabVar['tabRoute'];
				$class = $controllerName . 'Controller';
				$mainController = $this->context . '/' . $this->theme . '/controllers/' . $class . '.php';
				include_once $mainController;
				$method = $this->controllers[$controllerName];
				$instance = $class::getInstance($class);
				$instance->caller($args);
				/* TODO put here the call of the methd which call other some methods defined in classController */
				return $instance->$method($tpl);
			}
			else
				return $this->parser(getContentFile($tpl));		
		}
		
		private function funcInclude($value)
		{
			$file = $this->context . '/' . $this->theme . '/' . preg_replace('/\[#(.*)]/', '$1', $value) . '.tpl';
			return $this->getHTML($file);
		}
		
		private function funcFunction($value = null)
		{
			$func = explode(':', preg_replace('/\[\?(.*:?.*)]/', '$1', $value));
			$exec = array_shift($func);
			$args[0] = $this->context . '/' . $this->theme;
//			$args[0] = isset($func[1]) ? $func[1] . '/' . $this->theme : $args[0];
			$args[1] = &$this->listIf;
			$args[2] = &$this;
			$args[3] = &self::$tabVar['tabRoute'];
			if (isset($func[1]))
			{
				$args[4] = $func;
				$args[0] = implode('/', $func);
			}
			
			if (function_exists($exec))
				return $exec($args);
		}
		
		private function funcVariable($value)
		{
			$var = preg_replace('/\[\$(.*)]/', '$1', $value);
			if (isset(self::$tabVar[$var]))
				return self::$tabVar[$var];
			return 'This value doesn\'t exist.';
		}
		
		public function parser($contentTpl)
		{
			$html = '';
			$tabAction['#'] = 'funcInclude';
			$tabAction['?'] = 'funcFunction';
			$tabAction['$'] = 'funcVariable';

			preg_match_all('/<\?php[\s\S]*\?>|\[.*]|<.*\/>|<.*>.*<\/.*>|<.*>|<\/.*>|.*/U', $contentTpl, $array);
			$array = $array[0];
			foreach ($array as $element)
			{
//				echo end($this->listIf) . ' --- ' . htmlspecialchars($element) . '<br />';
				if (isset($element[1]) && isset($tabAction[$element[1]]))
				{
					if ($element[0] == '[')
					{
						$func = $tabAction[$element[1]];
						$return = $this->$func($element);
						if(end($this->listIf) === 'true')
							$html .= $return;
					}
					else if(end($this->listIf) === 'true')
						$html .= eval(preg_replace('/<\?php(.*)\?>/', '$1', preg_replace('/\n/', ' ', $element)));
				}
				else if (end($this->listIf) === 'true')
				{
					if (preg_match('/.*\[.*].*/', $element))
					{
						$tag = preg_replace('/.*(\[.*]).*/', '$1', $element);
						$func = $tabAction[$tag[1]];
						$element = preg_replace('/(.*)\[.*](.*)/', '$1' . $this->$func($tag) . '$2', $element);
					}
					$html .= $element;
				}
			}
			return $html;
		}

		public function display()
		{
			self::$tabVar['tabRoute'] = RequestHandler::getTabRoute();
			$prev = RequestHandler::getPrev();
			if (isset(self::$tabVar['tabRoute'][0]))
			{
				if (isset($this->contexts[self::$tabVar['tabRoute'][0]]))
				{
					$this->context = $this->contexts[self::$tabVar['tabRoute'][0]];
					if (isset(self::$tabVar['tabRoute'][1]))
						$this->page = self::$tabVar['tabRoute'][1];
				}
				else
					$this->page = self::$tabVar['tabRoute'][0];
			}
			if (isset($this->pages[$this->context][$this->page]))
				$this->page = $this->page . '.' . $this->pages[$this->context][$this->page];
			else
				$this->page = '404.tpl';
//			echo $this->context . '/' . $this->theme . '/' . $this->page . '<br />';

			self::$tabVar['page'] = $this->getHTML($this->context . '/' . $this->theme . '/' . $this->page);
			self::$tabVar['description'] = 'defaultDescription';
			self::$tabVar['title'] = 'defaultTitle';
			// TODO find a way to fill these two previous variables with data in tpl files ? May be thanks to php interpreter ? Or with an other verb ?
			// TODO idem for FB markup and other markup like it, which they will be implemented later (not ptiority)
			echo $this->getHTML($this->context . '/' . $this->theme . '/' . $this->context . '.tpl');
		}

		private static $tabVar;
		private $listIf;
//		private $tabRoute;

		private $controllers;
		private $contexts;
		private $context;
		private $theme;
		private $pages;
		private $page;
		private static $singleton;
	}

?>
