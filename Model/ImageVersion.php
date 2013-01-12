<?php
class ImageVersion extends AppModel {

	var $name = 'ImageVersion';

	//The Associations below have been created with all possible keys, those that are not needed can be removed
	var $belongsTo = array(
			'Image' => array('className' => 'Image',
								'foreignKey' => 'image_id',
								'conditions' => '',
								'fields' => '',
								'order' => ''
			)
	);
	
	

}
?>