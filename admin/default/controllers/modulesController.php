<?php

	class modulesController extends Controller
	{
		protected function __construct()
		{
			$this->callList[] = 'setList';
			$this->callList[] = 'installModule';
			$this->callList[] = 'uninstallModule';
			$this->callList[] = 'chooseDisplay';
			/* Put some method call here which are already in function.php */
		}
		
		protected function setList(&$args)
		{
			$this->listMod = getContentDir('modules');
		}

		protected function installModule(&$args)
		{
			if (htmlget(${$install = 'install'}))
			{
				include_once 'modules/' . $install . '/' . $install . '.php';
				$instance = $install::getInstance($install);
				$instance->install();
			}
		}

		protected function uninstallModule(&$args)
		{
			if (htmlget(${$uninstall = 'uninstall'}))
			{
				include_once 'modules/' . $uninstall . '/' . $uninstall . '.php';
				$instance = $uninstall::getInstance($uninstall);
				$instance->uninstall();
			}
		}
		
		protected function chooseDisplay(&$args)
		{
			if (isset($args[3][2]))
				$this->display = $this->displayConfiguration($args[3][2]);
			else
				$this->display = $this->listModules();
		}
		
		private function listModules()
		{
			$this->title = 'Module handling';
			$display = '';
			foreach ($this->listMod as $mod)
			{
				include_once 'modules/' . $mod . '/' . $mod . '.php';
				$install = '<a href="./?install=' . $mod . '">install</a>';
				if ($mod::isInstalled())
					$install = '<a href="./' . $mod . '">configure</a> <a href="./?uninstall=' . $mod . '">uninstall</a>';
				$display .= $mod . ' ' . $install . '<hr />';
			}
			return $display;
		}
		
		private function displayConfiguration($module)
		{
			$this->title = 'Configure ' . $module . ' module';
			$display = '<a href="../">back</a><hr />';
			include_once 'modules/' . $module . '/' . $module . '.php';
			$instance = $module::getInstance($module);
			$display .= $instance->getConfig();
			return $display;
		}
		
		public function hook_displayTitle(&$args)
		{
			return $this->title;
		}
		
		public function hook_displayModules(&$args)
		{
			return $this->display;
		}
		
		private $listMod;
		private $title;
		private $display;
	}
?>
