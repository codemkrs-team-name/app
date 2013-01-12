<?php
class Tag extends AppModel {

	public $name = 'Tag';
	public $order = array('count' => 'DESC');
	public $hasAndBelongsToMany = array('Project');	
	
}//end class
?>