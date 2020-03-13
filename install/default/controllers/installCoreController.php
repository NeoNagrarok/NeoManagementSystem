<?php

	class installCoreController extends Controller
	{
		protected function __construct()
		{
			/* Put some method call here which are already in function.php */
		}
			
		public function hook_start()
		{
			include_once 'core/security/security.php';
			if (!htmlget(${$remove = 'remove'}))
			{
				if (htmlget(${$start = 'start'}))
				{
					if (!DBTools::isInstalled())
					{
						$dbObj = DBTools::getInstance();
						$dbObj->install();
						$dbObj->defaultLanguageSetting();
						$dbObj->defaultTheme();
						$dbObj->installDone();
						return '<a href="?">Back</a><br />installed<br />
							<a href="/admin">Back office</a><br />
							';
					}
					header('location: ./');
					exit;
				}
				return '<a href="?start=">Install</a><br />';
			}
			return '';
		}
		
		function hook_remove()
		{
			// TODO need to verify if we already have installed the cms
			include_once 'core/security/security.php';
			if (!htmlget(${$start = 'start'}))
			{
				if (htmlget(${$remove = 'remove'}))
				{
					if (DBTools::isInstalled())
					{
						$dbObj = DBTools::getInstance();
						$dbObj->uninstall();
						return '<a href="?">Back</a><br />Uninstalled<br />';
					}
					header('location: ./');
					exit;
				}
				return '<a href="?remove=">Uninstall</a><br />';
			}
			return '';
		}
	}
