<?php

class VenuesController extends AppController {

	public $name = 'Venues';
	

	public function import()
	{
		$this->Venue->importCsv();			
	
	
		$this->autoRender = false;
	}
	
	


}//end class
?>