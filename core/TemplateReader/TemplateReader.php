<?php

	class TemplateReader
	{
		private function __construct()
		{
//			echo 'TemplateReaderConstruct<br />';
			$this->listIf['__first__'] = 'true';
			
			$this->context = 'themes';
			$this->theme = 'default';
			if (DBTools::isInstalled())
				$this->theme = DBTools::getMeta('theme');
			$this->page = 'home';
			
			/* NOTE May be main routes must be in database ? I don't know */
			$routes = json_decode(getContentFile('routes.json'), true);
			$this->controllers = $routes['controllers'];
			$this->contexts = $routes['contexts'];
			$this->pages = $routes['pages'];

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
				$this->currentControllers[$class] = $class;;
				$method = $this->controllers[$controllerName];
				$instance = $class::getInstance($class);
				$instance->caller($args);
				/* TODO put here the call of the method which call other some methods defined in classController */
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
//			echo '<pre>';
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
			
			if (isset($this->currentControllers))
			{
				foreach ($this->currentControllers as $controller)
				{
					$instance = $controller::getInstance($controller);
					$methodDisplay = '';
					if (method_exists($instance, 'hook_' . $exec))
					{
//						echo '::: controller method :::<br />' . $exec . '<br />';
						$exec = 'hook_' . $exec;
						$methodDisplay .= $instance->$exec($args);
					}
					$mod = null;
					if (ModuleGlobalController::getModule('hook_' . $exec, $mod))
					{
//						echo '::: module method :::<br />' . $exec . '<br />';
						$exec = 'hook_' . $exec;
						if ($mod)
							for($i = 0;isset($mod[$i]); $i++)
							{
								include_once 'modules/' . $mod[$i] . '/' . $mod[$i] . '.php';
								$methodDisplay .= $mod[$i]::getInstance($mod[$i])->$exec($args);
							}
						// TODO if i think right, here there is hook_module vefication. We need to change the install and may be the uninstall methods in order to make a good table for this verification.
					}
					if ($methodDisplay)
						return $methodDisplay;
				}
			}
			if (function_exists($exec))
			{
//				echo '--- global hook ---<br />' . $exec . '<br />';
				return $exec($args);
			}
//			echo '</pre>';
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

		private $controllers;
		private $currentControllers;
		private $contexts;
		private $context;
		private $theme;
		private $pages;
		private $page;
		private static $singleton;
	}

?>
