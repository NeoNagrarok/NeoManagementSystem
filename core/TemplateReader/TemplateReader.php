<?php
	include_once 'core/files/files.php';
	include_once 'core/TemplateReader/functions.php';

	class TemplateReader
	{
		public function __construct()
		{
//			echo 'TemplateReaderConstruct<br />';
			$this->listIf['__first__'] = 'true';
		}
		
		public function setPrev($prev)
		{
			$this->tabVar['prev'] = $prev;
		}
		
		public function setTabRoute($tabRoute)
		{
			$this->tabVar['tabRoute'] = $tabRoute;
		}
		
		public function setPage($context, $page, $theme)
		{
//			echo $context . '/' . $theme . '/' . $page . '<br />';
			$this->theme = $theme;
			$this->tabVar['page'] = $this->getHTML($context, $context . '/' . $theme . '/' . $page);
			$this->tabVar['description'] = 'defaultDescription';
			$this->tabVar['title'] = 'defaultTitle';
			// TODO find a way to fill these two previous variables with data in tpl files ? May be thanks to php interpreter ? Or with an other verb ?
			// TODO idem for FB markup and other markup like it, which they will be implemented later (not ptiority)
		}
		
		public function getHTML($context, $tpl)
		{
//			echo $tpl . '<br />';
			$this->context = $context;
			$contentTpl = getContentFile($tpl);
//			echo htmlspecialchars($contentTpl) . '<hr />';
			$html = $this->parser($contentTpl);
			return $html;
		}
//		
//		public function setTheme($theme)
//		{
//			$this->theme = $theme;
//		}
		
		private function funcInclude($value)
		{
			$file = $this->context . '/' . $this->theme . '/' . preg_replace('/\[#(.*)]/', '$1', $value) . '.tpl';
			return $this->getHTML($this->context, $file);
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
				return $func[0]($this->tabVar['prev'], $args);
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

		private $context;
		private $tabVar;
		private $theme;
		private $listIf;
		private $tabRoute;
	}

?>
