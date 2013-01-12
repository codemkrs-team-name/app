<?php

App::uses('AppHelper', 'View/Helper');

class FlashHelper extends AppHelper {

	var $helpers = array('Session');
	var $view;
  
     function __construct($view, $settings = array()) {
        parent::__construct($view, $settings);
		
		$this->view = $view;
     
    } 
  
	function show($message = "", $type = "alert"){
		 	 
		$messages = array();
		
  		if (empty($message)){
			// Get the messages from the session
			//$messages = $this->Session->read('messages');
			$messages = CakeSession::read('messages');
			
    		// Clear the messages array from the session
    		//$this->Session->delete('messages'); 		session helper can't write in cake 2.0	
			CakeSession::delete('messages');
			
		} else if ($message == 'cakeMessages') {
		
			$cakeMessages = CakeSession::read('Message');
			CakeSession::delete('Message');
			if (is_array($cakeMessages)){
				foreach($cakeMessages as $cakeType => $cakeMessage)
				{
					$messages[$type] = array($cakeMessage['message']);
				}
			}
		} else {		
			$messages[$type] = array($message);
		}
		
		
		$html = "";
		
		if(!empty($messages)){	
		
		//debug($messages);
    		foreach ($messages as $type => $typeMessages){		
			
			
				$count = count($typeMessages);
				$counter = 0;
				$messageHtml = "";
      			foreach ($typeMessages as $message){ 
       				 if (!empty($message)) {
					 	$class = "";
					 	if (($counter + 1) == $count)	$class = " last";
          				$messageHtml .= "<div class='message" . $class . "'>$message</div>\n";
        			}        
					$counter++;
      			}	
				$html .= $this->_View->element('flash', array('message' => $messageHtml, 'type' => $type)); 		
    		}//end foreach
			
    	}//end if

	 	echo $this->output( $html );      
	}//end show
  
}//end class 



?>