<?php

	class contentController extends Controller
	{
		protected function __construct()
		{
			$this->callList[] = 'insertRadio';
			$this->callList[] = 'insertCheckbox';
			$this->callList[] = 'insertField';
			$this->callList[] = 'deleteOption';
			$this->callList[] = 'deleteCheckbox';
			$this->callList[] = 'deleteField';
			/* Put some method call here which are already in function.php */
		}
		
		protected function insertRadio(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			if (!isset($args[3][2]))
			{
				header('location: ' . $prev);
				exit;
			}
			if (!in_array('addRadioButton', $_POST))
				return '';
			$arrKeys = array_keys($_POST, 'addRadioButton');
			include_once 'libs/form/form.php';
			if (!getInputRandomSession())
				return '';
			foreach ($arrKeys as $key)
			{
				$row = $args[3][2];
				$field = str_replace('addRadio', '', urldecode($key));
				$option = $_POST[$key . 'Content'];
				if(!$option)
					return '';
				$db = DBTools::getDB(__DB__);
				$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
				$prepReq->bindParam(':type', $row);
				$prepReq->execute();
				$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
				if (!isset($json[$field]['options']))
					$json[$field] = array_merge($json[$field], ['options' => [0 => $option]]);
				else
					$json[$field]['options'][] = $option;
		//		echo '<pre>';
		//		print_r($json);
		//		echo '</pre>';
				$jsonEncoded = json_encode($json);
		//		echo $jsonEncoded;
				$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
				$prepReq->bindParam(':type', $row);
				$prepReq->bindParam(':inner', $jsonEncoded);
				$prepReq->execute();
			}
			return '';
		}
		
		protected function insertCheckbox(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			if (!isset($args[3][2]))
			{
				header('location: ' . $prev);
				exit;
			}
			if (!in_array('addCheckboxButton', $_POST))
				return '';
			$arrKeys = array_keys($_POST, 'addCheckboxButton');
		//	echo '<pre>';
		//	print_r($arrKeys);
		//	echo '</pre>';
			include_once 'libs/form/form.php';
			if (!getInputRandomSession())
				return '';
			foreach ($arrKeys as $key)
			{
				$row = $args[3][2];
				$field = str_replace('addCheckbox', '', urldecode($key));
				$checkbox = $_POST[$key . 'Content'];
		//		echo $row . ' ' . $field . ' ' . $checkbox;
				if(!$checkbox)
					return '';
				$db = DBTools::getDB(__DB__);
				$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
				$prepReq->bindParam(':type', $row);
				$prepReq->execute();
				$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
				if (!isset($json[$field]['checkboxes']))
					$json[$field] = array_merge($json[$field], ['checkboxes' => [0 => $checkbox]]);
				else
					$json[$field]['checkboxes'][] = $checkbox;
		//		echo '<pre>';
		//		print_r($json);
		//		echo '</pre>';
				$jsonEncoded = json_encode($json);
		//		echo $jsonEncoded;
				$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
				$prepReq->bindParam(':type', $row);
				$prepReq->bindParam(':inner', $jsonEncoded);
				$prepReq->execute();
			}
			return '';
		}
		
		protected function insertField(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			if (!isset($args[3][2]))
			{
				header('location: ' . $prev);
				exit;
			}
			include_once 'libs/form/form.php';
			if (!htmlpost(${$lastFieldAdded = 'lastFieldAdded'}) || !htmlpost(${$type = 'type'}) || !htmlpost(${$order = 'order'}) || !$lastFieldAdded || !$order)
				return '';
			if (!getInputRandomSession())
				return '';
			$db = DBTools::getDB(__DB__);
			$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
			$prepReq->bindParam(':type', $args[3][2]);
		//	echo '<pre>';
			$prepReq->execute();
		//	echo '</pre>';
			$fetchResult = $prepReq->fetchAll();
			if (isset($fetchResult[0]) && isset($fetchResult[0]['inner']))
				$json = json_decode($fetchResult[0]['inner'], true);
			else
				$json = [];
		//	print_r($json);
		//	if (!$json)
		//		$json = [];

		//	echo $lastFieldAdded . ' ' . $type;

			$lastFieldAdded = preg_replace('/[^0-9a-zA-Z ]*/', '', $lastFieldAdded);
			if (!isset($json[$lastFieldAdded]))
				$json = array_merge($json, [$lastFieldAdded => ['type' => $type, 'order' => $order]]);
			else
			{
				$json[$lastFieldAdded]['type'] = $type;
				$json[$lastFieldAdded]['order'] = $order;
			}

		//	print_r($json);

			$jsonEncoded = json_encode($json);
		//	echo $jsonEncoded;
			$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
			$prepReq->bindParam(':type', $args[3][2]);
			$prepReq->bindParam(':inner', $jsonEncoded);
			$prepReq->execute();
			return '';
		}
		
		protected function deleteOption(&$args)
		{
			if (!isset($args[3][2]))
				return '';
			if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete-opt')
				return '';
			$db = DBTools::getDB(__DB__);
			$type = urldecode($args[3][2]);
			$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel where type=:type");
			$prepReq->bindParam(':type', $type);
			$prepReq->execute();
			$json = json_decode($prepReq->fetchAll()[0]['inner'], true);

		//	echo urldecode($args[3][4]);
			$arrRowOption = explode('-', urldecode($args[3][4]));
		//	echo '<pre>';
		//	print_r($json);
		//	echo '</pre>';
			
			if (isset($json[$arrRowOption[0]]))
				if (($test = array_search($arrRowOption[1], $json[$arrRowOption[0]]['options'])) !== false)
					unset($json[$arrRowOption[0]]['options'][$test]);
			
			$jsonEncoded = json_encode($json);
		//	echo $jsonEncoded;
			$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel SET `inner` = :inner WHERE type = :type");
			$prepReq->bindParam(':type', $type);
			$prepReq->bindParam(':inner', $jsonEncoded);
			$prepReq->execute();
			header('location: ../../');
			return '';
		}

		protected function deleteCheckbox(&$args)
		{
			if (!isset($args[3][2]))
				return '';
			if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete-chk')
				return '';
			$db = DBTools::getDB(__DB__);
			$type = urldecode($args[3][2]);
			$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel where type=:type");
			$prepReq->bindParam(':type', $type);
			$prepReq->execute();
			$json = json_decode($prepReq->fetchAll()[0]['inner'], true);

		//	echo urldecode($args[3][4]);
			$arrRowCheckbox = explode('-', urldecode($args[3][4]));
		//	echo '<pre>';
		//	print_r($json);
		//	echo '</pre>';
			
			if (isset($json[$arrRowCheckbox[0]]))
				if (($test = array_search($arrRowCheckbox[1], $json[$arrRowCheckbox[0]]['checkboxes'])) !== false)
					unset($json[$arrRowCheckbox[0]]['checkboxes'][$test]);
			
			$jsonEncoded = json_encode($json);
		//	echo $jsonEncoded;
			$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id = link.id_contentModel SET `inner` = :inner WHERE type = :type");
			$prepReq->bindParam(':type', $type);
			$prepReq->bindParam(':inner', $jsonEncoded);
			$prepReq->execute();
			header('location: ../../');
			return '';
		}
		
		protected function deleteField(&$args)
		{
			if (!isset($args[3][2]))
				return '';
			if (!isset($args[3][3]) || !isset($args[3][4]) || $args[3][3] != 'delete')
				return '';
			$db = DBTools::getDB(__DB__);
			$type = urldecode($args[3][2]);
			$prepReq = $db->prepare("select `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
			$prepReq->bindParam(':type', $type);
			$prepReq->execute();
			$json = json_decode($prepReq->fetchAll()[0]['inner'], true);
			if (isset($json[urldecode($args[3][4])]))
				unset($json[urldecode($args[3][4])]);
		//		print_r($json);
			$jsonEncoded = json_encode($json);
		//	echo $jsonEncoded;
			$prepReq = $db->prepare("UPDATE " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel SET `inner` = :inner WHERE type = :type");
			$prepReq->bindParam(':type', $type);
			$prepReq->bindParam(':inner', $jsonEncoded);
			$prepReq->execute();
			header('location: ../../');
			return '';
		}
	}
