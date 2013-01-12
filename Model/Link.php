<?php
class Link extends AppModel {

	public $name = 'Link';
	
	public $hasAndBelongsToMany = array('Venue');
	
}
	
	