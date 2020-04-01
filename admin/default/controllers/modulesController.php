<?php

	class modulesController extends Controller
	{
		protected function __construct()
		{
			/* Put some method call here which are already in function.php */
		}
		
		protected function getList(&$args)
		{
			
		}
		
		public function hook_listModules(&$args)
		{
			return 'yoyo';
		}
	}
?>
