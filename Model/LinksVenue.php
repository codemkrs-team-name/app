<?php
class LinksVenue extends AppModel {

	public $name = 'LinksVenue';
	
	public $belongsTo = array('Link', 'Venue');
	
}
	
	