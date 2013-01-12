<?php

class EventsController extends AppController {

	public $name = 'Events';
	
	
	public $paginate = array(
		'Event' => array(						   
        	'limit' => 100
		)	
	);	

	public function index()
	{
				
		$events = $this->paginate('Event');	
		$this->set('results', $events);
		
	}
	
	


}//end class
?>