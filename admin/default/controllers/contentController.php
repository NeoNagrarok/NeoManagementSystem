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
			$this->callList[] = 'postForm';
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
		
		protected function postForm()
		{
			/* TODO we have to think to make an update if the entry already exists !!! */
			$result = [];
			/* TODO remove true from next condition in order to finalize data saving */
			if (getInputRandomSession())
			{
				if (isset($_POST['save']))
				{
					foreach($_POST as $key => $value)
						if ($key != 'randomPost' && $key != 'save' && strpos($key,'add') === false && $key != 'id_contentModel')
							$result[$key] = $value;
					/* TODO we have to think to make an update if the entry already exists !!! */
					/* TODO WARNING ! Be careful if we add or delete any field with update !!! */
					$dbt = DBTools::getInstance();
					$dbt->insert(__DB_PREFIX__ . 'content', [
						'id_contentModel'	=> $_POST['id_contentModel'],
						'code_chmod'		=> '777', // TODO change chmod ...
						'formData'			=> json_encode($result)
					]);
				}
			}
		}
		
		public function hook_getContentTitle(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			if (!isset($args[3][2]))
			{
				header('location: ' . $prev);
				exit;
			}
			return urldecode($args[3][2]);
		}
		
		public function hook_listContentForm(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			if (!isset($args[3][2]))
			{
				header('location: ' . $prev);
				exit;
			}
			$contentType = $args[3][2];
		//	echo urldecode($contentType) . '<hr />';
			$db = DBTools::getDB(__DB__);
			$prepReq = $db->prepare("select id_contentModel, type, `inner` from " . __DB_PREFIX__ . "contentModel as content left join " . __DB_PREFIX__ . "link_contentModel_lang as link on content.id=link.id_contentModel where type=:type");
			$prepReq->bindParam(':type', $contentType);
			$prepReq->execute();
			$fetchResult = $prepReq->fetchAll();
			if (empty($fetchResult))
			{
				header('location: ../../');
				exit;
			}
		//	echo '<pre>';
		//	print_r($fetchResult);
		//	echo '</pre>';
		//	die();
			if (!isset($fetchResult[0]) || !isset($fetchResult[0]['inner']))
				return '';
			$json = json_decode($fetchResult[0]['inner'], true);
			uasort($json, function ($a, $b)
			{
				return $a['order'] <=> $b['order'];
			});
		//	echo '<pre>';
		//	print_r($json);
		//	echo '</pre>';
			$result = '<form action="./" method="post">';
			$result .= '<input type="hidden" name="id_contentModel" value="'.$fetchResult[0]['id_contentModel'].'" />';
			if ($json)
				foreach ($json as $key => $value)
				{
					$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm/' . urlencode($key) . '">( - )</a> ';
					if ($value['type'] == 'text' || $value['type'] == 'email' || $value['type'] == 'number' || $value['type'] == 'url' || $value['type'] == 'phone' || $value['type'] == 'date' || $value['type'] == 'color' || $value['type'] == 'file')
					{
						$result .= '<label for="' . $key . '">' . $key . ' (' . $value['order'] . ') : <br />';
						$result .= '<input class="indent" type="' . $value['type'] . '" name="' . $key . '" id="' . $key . '" />';
						$result .= '</label>';
					}
					else if ($value['type'] == 'textarea')
					{
						$result .= '<label for="' . $key . '">' . $key . ' (' . $value['order'] . ') :<br />';
						$result .= '<textarea class="indent" name="' . $key . '" id="' . $key . '"></textarea>';
						$result .= '</label>';
					}
					else if($value['type'] == 'radio')
					{
						$result .= '' . $key . '(' . $value['order'] . ')';
						$result .= '<br />';
						$result .= '<span class="indent radio">';
						if (isset($value['options']))
							foreach($value['options'] as $option)
							{
								// TODO $key => urlencode();
								$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm-opt/' . urlencode($key) . '-' . urlencode($option) . '">( - )</a> ';
								$result .= '<label for="' . urlencode($key) . $option . '">' . $option . ' : ';
								$result .= '<input type="' . $value['type'] . '" name="' . urlencode($key) . '" id="' . $key . urlencode($option) . '" value="' . $option . '" />';
								$result .= '</label>';
								$result .= '<br />';
								if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm-opt' && urldecode($args[3][4]) == $key . '-' . $option)
									$result .= 'Confirmer la suppression de ' . $option . ' ? <a href="../../delete-opt/' . urlencode($key) . '-' . urlencode($option) . '">Oui</a> <a href="../../">Non</a><br />';
							}
						$result .= '<label for="addRadio' . urlencode($key) .'Content">Add option : ';
						$result .= '<input type="text" name="addRadio' . urlencode($key) .'Content" id="addRadio' . urlencode($key) .'Content" />';
						$result .= '<button type="submit" name="addRadio' . urlencode($key) . '" value="addRadioButton">Add</button>';
						$result .= '</label>';
						$result .= '</span>';
					}
					else if ($value['type'] == 'checkbox')
					{
						// TODO display correctly checkboxes
						$result .= '' . $key . '(' . $value['order'] . ')';
						$result .= '<br />';
						$result .= '<span class="indent checkbox">';
						if (isset($value['checkboxes']))
							foreach($value['checkboxes'] as $checkbox)
							{
								// TODO $key => urlencode();
								$result .= '<a href="' . $prev . 'content/' . $contentType . '/confirm-chk/' . urlencode($key) . '-' . urlencode($checkbox) . '">( - )</a> ';
								$result .= '<label for="' . urlencode($key) . '-' .  urlencode($checkbox) . '">' . $checkbox . ' : ';
								$result .= '<input type="' . $value['type'] . '" name="' . urlencode($key) . '-' . urlencode($checkbox) . '" id="' . urlencode($key) . '-' .  urlencode($checkbox) . '" value="'.$checkbox.'" />';
								$result .= '</label>';
								$result .= '<br />';
								if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm-chk' && urldecode($args[3][4]) == $key . '-' . $checkbox)
									$result .= 'Confirmer la suppression de ' . $checkbox . ' ? <a href="../../delete-chk/' . urlencode($key) . '-' . urlencode($checkbox) . '">Oui</a> <a href="../../">Non</a><br />';
							}
						$result .= '<label for="addCheckbox' . urlencode($key) .'Content">Add checkbox : ';
						$result .= '<input type="text" name="addCheckbox' . urlencode($key) .'Content" id="addCheckbox' . urlencode($key) .'Content" />';
						$result .= '<button type="submit" name="addCheckbox' . urlencode($key) . '" value="addCheckboxButton">Add</button>';
						$result .= '</label>';
						$result .= '</span>';
					}
					else if ($value['type'] == 'file')
					{
						$result .= '';
					}
					else
					{
						$result .= 'toto';
					}
					$result .= '<br />';
					if (isset($args[3][3]) && isset($args[3][4]) && $args[3][3] == 'confirm' && urldecode($args[3][4]) == $key)
							$result .= 'Confirmer la suppression de ' . $key . ' ? <a href="../../delete/' . urlencode($key) . '">Oui</a> <a href="../../">Non</a><br />';
				}
			$result .= setInputRandomSession();
			$result .= '<button type="submit" name="save">Save</button>';
			$result .= '</form>';
			return $result;
		}
		
		public function hook_getSemiPrev(&$args)
		{
			if (count(explode('/', RequestHandler::getPrev())) >= 5)
				return '../../';
			return './';
		}
		
		public function hook_lastFieldAdded(&$args)
		{
			if (isset($_POST['addField']))
			{
				$lastFieldAdded = htmlspecialchars($_POST['addField']);
				$form  = '<form method="post" action="./">';
				$form .= '<p>Type ' . $lastFieldAdded . '</p>';
				$form .= '<label for="lastFieldAdded" title="It\'s name will be also it\'s class and it\'s id">Name : <input type="text" id="lastFieldAdded" name="lastFieldAdded" />';
				$form .= '</label>';
				$form .= '<label for="order">Order : <input type="number" id="order" name="order" />';
				$form .= '</label>';
				$form .= '<input type="hidden" name="type" value="' . $lastFieldAdded . '" />';
				$form .= '<br />';
				$form .= '<button type="submit">Add</button>';
				$form .= removeRefreshBehavior($args);
				$form .= '</form>';
				return $form;
			}
		}
	}
