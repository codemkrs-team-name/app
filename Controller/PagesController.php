<?php
class PagesController extends AppController {

	public $name = 'Pages';
	public $uses = array('User');

	function pages_home() {
				
		$this->render('/Pages/home');	
	}
	
	
	function display() {
		$path = func_get_args();

		if (!count($path)) {
			$this->redirect('/');
		}
		
		$count = count($path);
		$page = $subpage = $title = null;

		if (!empty($path[0])) {
			$page = $path[0];
		}
		if (!empty($path[1])) {
			$subpage = $path[1];
		}
		if (!empty($path[$count - 1])) {
			$title_for_layout = Inflector::humanize($path[$count - 1]);
		}
		$this->set(compact('page', 'subpage', 'title_for_layout'));
		
		$function = 'pages_'.$page;
		if (method_exists($this, $function)) {
    		$this->$function($subpage);
  		} else {
			//$this->render(join('/', $path));
			$this->render('/Pages/' . $page);
		}
	}
}
