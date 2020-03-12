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
			$this->contexts['admin'] = 'admin';
			$this->contexts['install'] = 'install';
			/* TODO get the good list of pages from where data are stored !!! */
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
		
		public function getHTML($tpl)
		{
//			echo $tpl . '<br />';
			$contentTpl = getContentFile($tpl);
//			echo htmlspecialchars($contentTpl) . '<hr />';
			$html = $this->parser($contentTpl);
			return $html;
		}
		
		private function funcInclude($value)
		{
			$file = $this->context . '/' . $this->theme . '/' . preg_replace('/\[#(.*)]/', '$1', $value) . '.tpl';
			return $this->getHTML($file);
		}
		
		private function funcFunction($value)
		{
			$func = explode(':', preg_replace('/\[\?(.*:?.*)]/', '$1', $value));
			$args[0] = 'themes/' . $this->theme;
			$args[0] = isset($func[1]) ? $func[1] . '/' . $this->theme : $args[0];
			$args[1] = &$this->listIf;
			$args[2] = &$this;
			$args[3] = &$this->tabVar['tabRoute'];
			if (function_exists($func[0]))
				return $func[0]($args);
		}
		
		private function funcVariable($value)
		{
			$var = preg_replace('/\[\$(.*)]/', '$1', $value);
			if (isset($this->tabVar[$var]))
				return $this->tabVar[$var];
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
			$tabRoute = RequestHandler::getTabRoute();
			$prev = RequestHandler::getPrev();
			if (isset($tabRoute[0]))
			{
				if (isset($this->contexts[$tabRoute[0]]))
				{
					$this->context = $this->contexts[$tabRoute[0]];
					if (isset($tabRoute[1]))
						$this->page = $tabRoute[1];
				}
				else
					$this->page = $tabRoute[0];
					/* TODO put here the code to choose an other admin theme if we had set an other theme for our cms ! */
			}
			$mainTpl = $this->context . '/' . $this->theme . '/index.tpl';
			if (isset($this->pages[$this->context][$this->page]))
				$this->page = $this->page . '.' . $this->pages[$this->context][$this->page];
			else
				$this->page = '404.tpl';
			$this->tabVar['tabRoute'] = $tabRoute;
	//		echo $context . '/' . $theme . '/' . $page . '<br />';
			$this->tabVar['page'] = $this->getHTML($this->context . '/' . $this->theme . '/' . $this->page);
			$this->tabVar['description'] = 'defaultDescription';
			$this->tabVar['title'] = 'defaultTitle';
			// TODO find a way to fill these two previous variables with data in tpl files ? May be thanks to php interpreter ? Or with an other verb ?
			// TODO idem for FB markup and other markup like it, which they will be implemented later (not ptiority)
	//		echo htmlspecialchars($TemplateReader->getHTML($mainTpl));
			echo $this->getHTML($mainTpl);
		}

		private $tabVar;
		private $listIf;
//		private $tabRoute;

		private $contexts;
		private $context;
		private $theme;
		private $pages;
		private $page;
		private static $singleton;
	}

?>
