<?php

	class adminController extends Controller
	{
		protected function __construct()
		{
			/* Put some method call here which are already in function.php */
			$this->callList[] = 'connect';
			$this->callList[] = 'disconnect';
		}
		
		protected function connect($args)
		{
			if (!DBTools::isInstalled())
			{
				session_destroy();
				header('location: /');
				exit;
			}
			if (explode('/', $args[0])[0] === 'admin' && !isset($_SESSION['logged']))
			{
				if (!isset($_POST['adId']) || !isset($_POST['adPw']))
				{
					/* TODO get the good error message depending on the language settled ! */
					$connectForm = '';
					if (isset($_GET['error']))
					{
						$arrayError[1] = 'Mauvais identifiants !';
						if (isset($arrayError[$_GET['error']]))
							$connectForm .= '<p>' . $arrayError[$_GET['error']] . '</p>';
					}
					$TemplateReader = $args[2];
					$prev = RequestHandler::getPrev();
					echo $TemplateReader->parser(getContentFile($args[0] . '/metaHead.tpl')) . bodyOn($prev, $args) . $TemplateReader->parser(getContentFile($args[0] . '/parts/connectForm.tpl')) . bodyOf($prev, $args);
					exit();
				}
				else
				{
					$adId = htmlspecialchars($_POST['adId']);
					$adPw = htmlspecialchars($_POST['adPw']);
					$log = $_SERVER['REMOTE_ADDR'] . '>' . date('Y-m-d H:i:s') . '>' . $adId .'>';
					/* TODO get real identifiants from the place where data are stored ! */
					$logged = true;
					if ($adId !== 'Test' || $adPw !== 'test')
						$logged = false;
					else
					{
						$_SESSION['logged'] = str_shuffle(random_bytes(8) . (~$adId) . random_bytes(8) . ~($adPw) . random_bytes(8));
						$_SESSION['rank'] = 'admin'; // TODO Get the right admin rank where data are stored
						/* TODO create good sessions corresponding to good permissions by example */
					}
					$log .= ($logged ? 'true' : 'false') . "\n";
					createFile('logs/connect.log', $log);
					if (!$logged)
						header('location: ./?error=1');
				}
			}
		}
		
		protected function disconnect($args)
		{
			if (isset($_SESSION['logged']))
				if (isset($_POST['disconnect']))
				{
					session_destroy();
					header('location: ./');
				}
		}
	}

?>
