<?php

	class addContentController extends Controller
	{
		protected function __construct()
		{
			$this->callList[] = 'addContent';
			/* Put some method call here which are already in function.php */
		}
		
		protected function addContent(&$args)
		{
			include_once 'core/form/form.php';
			if (!getInputRandomSession())
				return '';
			if (isset($_POST['contentTitle']) && isset($_POST['contentOrder']))
			{
				$title = urlencode($_POST['contentTitle']);
				$order = (int) $_POST['contentOrder'] * 10;
				$dbt = DBTools::getInstance();
				$dbt->insert(__DB_PREFIX__ . 'contentModel', ['order' => $order]);
				$dbt->insert(
					__DB_PREFIX__ . 'link_contentModel_lang',
					[
						'id_contentModel'	=> $dbt->lastInsertId(),
						'iso_code_lang'		=> DBTools::getMeta('defaultLanguage'),
						'type'				=> $title
					]
				);		
				header('location: ../content/' . $title);
				exit;
			}
			return '';
		}
	}

?>
