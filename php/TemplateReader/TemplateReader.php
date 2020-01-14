<?php
	include_once 'php/files/files.php';
	include_once 'php/TemplateReader/functions.php';
	
	// TODO make some tpl files for header, main, footer and test php injection with $ symbol. After may be create a php translater (see one of the next to do ;) )

	class TemplateReader
	{
		public function __construct()
		{
//			echo 'TemplateReaderConstruct<br />';
		}
		
		public function setPrev($prev)
		{
			$this->tabVar['prev'] = $prev;
		}
		
		public function setPage($context, $page)
		{
			$this->tabVar['page'] = $this->getHTML($context, $context . '/' . $page);
			$this->tabVar['description'] = 'defaultDescription';
			$this->tabVar['title'] = 'defaultTitle';
			// TODO find a way to fill these two previous variables with data in tpl files ? May be thanks to php interpreter ? Or with an other verb ?
			// TODO idem for FB markup and other markup like it, which they will be implemented later (not ptiority)
		}
		
		public function getHTML($context, $tpl)
		{
			$this->context = $context;
			$contentTpl = getContentFile($tpl);
//			echo htmlspecialchars($contentTpl) . '<hr />';
			$html = $this->parser($contentTpl);
			return $html;
		}
		
		private function funcInclude($value)
		{
			$file = $this->context . '/' . preg_replace('/\[#(.*)]/', '$1', $value) . '.tpl';
			return $this->getHTML($this->context, $file);
		}
		
		private function funcFunction($value)
		{
			$func = explode(':', preg_replace('/\[\?(.*:?.*)]/', '$1', $value));
			$arg = isset($func[1]) ? $func[1] : NULL;
			if (function_exists($func[0]))
				return $func[0]($this->tabVar['prev'], $arg);
		}
		
		private function funcVariable($value)
		{
			$var = preg_replace('/\[\$(.*)]/', '$1', $value);
			if (isset($this->tabVar[$var]))
				return $this->tabVar[$var];
			return 'This value doesn\'t exist.';
		}
		
		private function parser($contentTpl)
		{
			$html = '';
			$tabAction['#'] = 'funcInclude';
			$tabAction['?'] = 'funcFunction';
			$tabAction['$'] = 'funcVariable';

			preg_match_all('/<\?php[\s\S]*\?>|\[.*]|<.*\/>|<.*>.*<\/.*>|.*/U', $contentTpl, $array);
			$array = $array[0];
			foreach ($array as $element)
			{
				if (isset($element[1]) && isset($tabAction[$element[1]]))
					if ($element[0] == '[')
					{
						$func = $tabAction[$element[1]];
						$html .= $this->$func($element);
					}
					else
						$html .= eval(preg_replace('/<\?php(.*)\?>/', '$1', preg_replace('/\n/', ' ', $element)));
				else
				{
					// TODO do it if we have many tpl markup on the same line ...
					if (preg_match('/.*\[.*].*/', $element))
					{
						$tag = preg_replace('/.*(\[.*]).*/', '$1', $element);
						$func = $tabAction[$tag[1]];
						$element = preg_replace('/(.*)\[.*](.*)/', '$1' . $this->$func($tag) . '$2', $element);
					}
					$html .= $element;
				}
				// TODO find a way to put persistent data from tpl file in order to use theim may be in other tpl file and later ? Like mysql use ?
			}
			return $html;
		}

		private $context;
		private $tabVar;
		
	}

?>
