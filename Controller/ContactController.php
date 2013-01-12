<?php
class ContactController extends AppController {
	
	public $components = array('Email', 'Auth');
	
	public function index() {
		
		if($this->request->is('post')) {
			
			//debug($this->request->data);
		
	    	
	

	    	$this->Contact->set($this->request->data['Contact']);

	    	if($this->Contact->validates()) {
		
				$data = $this->request->data['Contact'];
				$message = $data['message'];
				$data = Sanitize::clean($data);
				$data['message'] = $message;
				
				$email = new CakeEmail('default');
				$email->from(array($data['from'] => $data['name']));
				$email->to('janders4@gmail.com');
				$email->subject("SpecialGoodStuff: " . $data['subject']);
				$email->viewVars(array('from' => $data['from'], 'name' => $data['name']));
		
				if ( $email->send( $data['message']) ){
					$this->flash("Thanks for sending me something.<br/>I'll get back to you as soon as possible.", "success");
					$this->redirect('/');
				} else {
					$this->flash("We had trouble sending your message. Please try again later.", "error");			
				}						
				
	    	} else {// throw errors from model
			
	    	}
		}//end if posted

	}//end index
	
	
	
	public function success()
	{
		
	}

}//end class
?>