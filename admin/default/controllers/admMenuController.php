<?php

	class admMenuController extends Controller
	{
		protected function __construct()
		{
			/* Put some method call here which are already in function.php */
		}
		
		public function hook_admMenu(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			$db = DBTools::getDB(__DB__);
			$return = '';
			foreach ($db->query('select type from ' . __DB_PREFIX__ . 'contentModel as content left join ' . __DB_PREFIX__ . 'link_contentModel_lang as link on content.id=link.id_contentModel order by `order`') as $row)
				$return .= '<a href="' . $prev . 'content/' . $row['type'] . '">' . urldecode($row['type']) . '</a>';
			return $return;
		}
		
		public function hook_addContentLink(&$args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			return '<a href="' . $prev . 'addContent">Add content</a>';
		}
		
		public function hook_modulesLink($args)
		{
			$prev = preg_replace('/^..\//', '', RequestHandler::getPrev());
			return '<a href="' . $prev . 'modules">Handle modules</a>';
		}
	}
?>
