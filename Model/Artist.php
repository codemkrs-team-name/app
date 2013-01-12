<?php
class Artist extends AppModel {

	public $name = 'Artist';
	
	public $belogsTo = array('Event');
	
}
	
	