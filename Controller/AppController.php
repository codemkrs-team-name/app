<?php

class AppController extends Controller {


	public $helpers = array('Html', 'Form', 'Js', 'Session', 'Cache', 'Flash', 'Paginator', 'Facebook.Facebook');
    public $components = array(
        'Session', 'RequestHandler', 'Wordpress', 
		'Facebook.Connect' => array('model' => 'User'),
        'Auth' => array(
            'loginRedirect' => array('controller' => 'pages', 'action' => 'display', 'home'),
            'logoutRedirect' => array('controller' => 'pages', 'action' => 'display', 'home')
        )
    );

    public function beforeFilter() {
		
		App::uses('Sanitize', 'Utility');

		//$this->Auth->authorize = array('Controller');
        //$this->Auth->allow('index', 'view', 'display');
		//
		$this->Auth->allow();
		$this->Auth->deny(array('create','edit','update'));
		
		$this->set('user', $this->Session->read('Auth.User'));
		
		//ADD JAVASCRIPT SUPPORT
		$this->RequestHandler->setContent('js', 'javascript');
		
		//ADD JSON SUPPORT
		$this->RequestHandler->setContent('json', 'text/x-json');
		
		if ($this->RequestHandler->ext == 'js'){

		} else if ($this->RequestHandler->ext == 'json'){  //if extention = .json or request was made via ajax
			Configure::write('debug', 0);
			$this->autoRender = false;
		} 		
		
		
    }

	function afterFilter(){
			
		if ($this->RequestHandler->ext == 'json'){  //if extention = .json or request was made via ajax

			
            $json = array();
			
   			if (isset($this->viewVars)) {
				$json = $this->viewVars;	
				//if a json variable has been set, pass the contents of that variable instead of all of the set variables
				if (isset($json['json']))	$json = $json['json'];
			}

			//add flashes to the json response
			$messages = $this->Session->read('messages');
			$this->Session->delete('messages');
			
			if (!empty($messages))	$json['flashes'] = $messages;
			if (empty($json))	$json = array("status" => "No data output.");
			
			
			
			
			//$this->set("json", $json);
			//$this->render('/json/default');

			//todo:  add check to see if things have rendered		
			$json = json_encode($json);
			
			$this->RequestHandler->respondAs("json");
			/*
			header("HTTP/1.0 200 OK");
			header("Pragma: no-cache");
			header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
			header('Content-Type: text/x-json');
			header("X-JSON: " . $json);		
			*/
            echo $json;

			//exit();
			//return false;			
		}
		
	} 

	public function renderWordpress($path = null, $throwException = true, $render = true){
		
		if (empty($path))	$path = $this->request->params['controller'] . '/' . $this->request->params['action'];				
		//if (! stristr($path, 'blog'))	$path = '/blog/' . $path;			
		//debug($path);
		$page = $this->Wordpress->get_page_by_path($path);
		//if it's empty and the path has index, try it without the index
		if (empty($page) && stristr($path, 'index'))	
		{
			$path = str_ireplace("/index", "", $path);			
			//debug($path);
			$page = $this->Wordpress->get_page_by_path($path);			
		}
		
		//debug($path);
		if (empty($page))	
		{			
			if ($throwException)	throw new MissingViewException(array('file' => "wordpress:$path"));	
			return false;
		}
		
		$m = new Mustache;

		if (!empty($_GET['debug']))	debug($this->viewVars);		
		$content = $m->render($page->post_content, $this->viewVars);
		
		$this->set('title_for_layout', $page->post_title);
		$this->set('content', $content); 
		if ($render)	$this->render('/Pages/wordpress');		
		
		return $content;		

	}

	
	/*
	* Add a message to the messages array in the session like this:
	* $this->flash( 'You are logged in.', 'success' );
	* possible types:  alert, success, error
	*/ 
	function flash( $message, $type = 'alert' ){
	
    	$old = $this->Session->read('messages');
    	$old[$type][] = $message;
		$this->Session->write('messages', $old );	
	}
	
	
	//set http status code and message
	//if message == 'flashes', uses current flashes for error message
	function setHeader($status, $statusText = ''){
		
		if ($statusText == 'flashes'){
			$messages = $this->Session->read('messages');
			$this->Session->delete('messages');
			
			$statusText = "";
			foreach ($messages as $message){
				$statusText .= $message . " - ";
			}
			$statusText = substr($statusText, 0, strlen($statusText) - 3);
		}					
		
		
		$this->header("HTTP/1.1 $status $statusText");
		
			
	}//end function

}//end appController