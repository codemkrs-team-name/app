<?php
class Event extends AppModel {

	public $name = 'Event';
	public $order = 'Event.start DESC';
	
	public $hasOne = array('Venue', 'Artist');



}
	
	