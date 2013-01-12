<?php


class Image extends AppModel {

	var $name = 'Image';


   	var $actsAs = array(
		'Media.Polymorphic',
		'Media.Transfer',
		'Media.Generator' => array('filterDirectory' => PROJECT_IMAGE_DIRECTORY)
	);

	var $validate = array(

       'url' => array(
			'checkImage' => array('rule' => array('checkImage', array(
					'mime' =>	array('image/jpg', 'image/jpeg', 'image/png', 'image/gif'),
					'height' => array(
						">=" => 300,
						"<=" => 2000
					),
					'width' => array(
						">=" => 300,
						"<=" => 2000									 
					)				
				), 'url'), //array of dimension rules keyed by 'height' and 'width', fieldName (defaults to 'file')
				'message' => 'Any image you upload must be a .jpeg, .gif, or .png, and be at least 300px wide X 300px high, and less than 2000px wide x 2000px high.',
				'allowEmpty' => true,				
				'last' => true					
			),		
			'resource' => array(
				'rule' => 'checkResource',
				'message' => 'The file you selected can not be uploaded.',
				'last' => true
			),		
            'access' => array(
				'rule' => 'checkAccess',
				'message' => 'The file you selected can not be uploaded. There was a problem with file permissions.',
				'last' => true							
			)
    	) ,	
	   
        'file' => array(
									
            'extension' => array(
				'rule' => array(
					'checkExtension',
   					array('bin', 'class', 'dll', 'dms', 'exe', 'lha'), //blacklist
                    array('jpg', 'jpeg', 'png', 'gif', 'tmp') //whitelist
                ),
				'message' => 'The image has to be a jpeg, png, or gif.',
				'last' => true
			),		
            'mimeType' => array(
				'rule' => array(
					'checkMimeType', 
					false, 
					array('image/jpg', 'image/jpeg', 'image/png', 'image/gif')
				),
				'message' => 'The image must be a jpeg, png, or gif.',
				'last' => true
			),
            'size' => array(
				'rule' => array('checkSize', '2M'),
				'message' => 'The file you selected is too large. The file must be less than 2 megabytes.',
				'last' => true						
			),            
			'checkImage' => array('rule' => array('checkImage', array(
					'height' => array(
						">=" => 300,
						"<=" => 2000
					),
					'width' => array(
						">=" => 300,
						"<=" => 2000									 
					)				
				), 'file'), //array of dimension rules keyed by 'height' and 'width', fieldName (defaults to 'file')
				'message' => 'Any image you upload must be at least 300px wide x 300px high, and less than 2000px wide x 2000px high.',
				'last' => true					
			),					
            'permission' => array(
				'rule' => array('checkPermission', '*'),
				'message' => 'The file you selected can not be uploaded. The file permissions of that resource are invalid.',
				'last' => true						
			),			
			'resource' => array(
				'rule' => 'checkResource',
				'message' => 'The file you selected can not be uploaded.',
				'last' => true
			),		
            'access' => array(
				'rule' => 'checkAccess',
				'message' => 'The file you selected can not be uploaded. There was a problem with file permissions.',
				'last' => true							
			)/*,
			'location' => array(
				'rule' => array('checkLocation', array(MEDIA_TRANSFER, '/tmp/', 'http://')), 
				'message' => 'Files can not be uploaded from the location in question.',
				'last' => true							
			)*/
    	) ,				  
						  
		'title' => array(
        	'rule' => array('maxLength', 200),	
            'message' => 'Maximum 200 characters.',
			'allowEmpty' => true					
		)		
	);
	
	
	var $hasMany = array(				 
			'ImageVersion' => array('className' => 'ImageVersion',
								'foreignKey' => 'image_id',
								'dependent' => true,
								'conditions' => '',
								'fields' => '',
								'order' => '',
								'limit' => '',
								'offset' => '',
								'exclusive' => '',
								'finderQuery' => '',
								'counterQuery' => ''
			)
	);
	
	var $hasAndBelongsToMany = array(
			
			'Project' => array('className' => 'Project',
						'joinTable' => 'images_projects',
						'foreignKey' => 'image_id',
						'associationForeignKey' => 'project_id',
						'unique' => true
			)
	);
	
	
	//type can be 'project' or 'user'
	//return image id if succesful, false or exception otherwise
	function upload($image = array(), $type = 'project', $typeId, $options = array())
	{
		if ( (empty($image)) || (empty($type)) || (empty($typeId)) 
			|| ( (empty($image['file'])) && (empty($image['url'])) ) )
		{
			//debug($image);
			//die('missing param');
			return false;
		}
		//key it with the model 
		$image = array('Image' => $image);
		
		$order = null;
		if (!empty($options['order']))	$order = $options['order'];
		$userId = CakeSession::read("Auth.User.id");	

		if ($type == 'user'){
			$base = USER_IMAGE_DIRECTORY . $typeId . DS;
		} else if ($type == 'project'){
			$base = PROJECT_IMAGE_DIRECTORY . $typeId . DS;	
		} 
		$imagePrefix = $typeId;

		$this->set($image);
		if(! $this->validates() ) 
		{
			//debug($image);
			//die('no validate');
			return false;
		}
		
		$file = false;	
		if (!empty($image['Image']['file']['tmp_name']))	
			$file = $image['Image']['file'];
		else if (!empty($image['Image']['url'])){
			$file = $image['Image']['url'];			
		}
		if (empty($file))
		{
			//debug($image);
			//die('no file');
			return false;	
		}
			

		$transferedFile = $this->transfer($file);			
		if (! $transferedFile){	
			//debug($image);
			//die('no transfer');
			return false;
		}

		$processes = array(
		
			'870' => array(
				'version' => 'huge',
				'destination' => $this->generateDestination($base , 'huge', $imagePrefix),
				'instructions' => array(
					'convert' => 'image/jpeg', 
					'fit' => array(860, 860)
				)										 
			),			
			'750' => array(
				'version' => 'large',
				'destination' => $this->generateDestination($base , 'large', $imagePrefix),
				'instructions' => array(
					'convert' => 'image/jpeg', 
					'fit' => array(750, 750)
				)
			),
			'500' => array(
				'version' => 'medium_large',
				'destination' => $this->generateDestination($base , 'medium_large', $imagePrefix),
				'instructions' => array(
					'convert' => 'image/jpeg', 
					'fit' => array(500, 500)
				)										 
			),			
			'250' => array(
				'version' => 'medium',
				'destination' => $this->generateDestination($base , 'medium', $imagePrefix),
				'instructions' => array(			
					'convert' => 'image/jpeg'
				)									  
			),
			'120' => array(
				'version' => 'small',
				'destination' => $this->generateDestination($base, 'small', $imagePrefix),
				'instructions' => array(
					'convert' => 'image/jpeg', 
					'zoomCrop' => array(120, 120)
				)									 
			),
			'80' => array(
				'version' => 'thumbnail',
				'destination' => $this->generateDestination($base , 'thumbnail', $imagePrefix),
				'instructions' => array(
					'convert' => 'image/jpeg', 
					'zoomCrop' => array(80, 80)
				)										 
			)
		);
		
				
		list($width, $height) = getimagesize($transferedFile);	
				
		$maxDimension = $width;
		if ($height > $width) $maxDimension = $height;
		
		
		if ($maxDimension >= 400)
		{
			$processes['250']['instructions']['zoomCrop'] = array(250,250);		
		}
		else
		{
			$processes['250']['instructions']['fit'] = array(250,250);					
		}
				
		$imageVersions = array();
		foreach($processes as $size => $process){					
		
			if ($size <= $maxDimension){
				
				$this->makeVersion($transferedFile, $process);
					
				if ($type == 'user')
				{
					$source = str_replace(DS, '/', DS . 'img' . DS . 'users' . DS . str_replace(USER_IMAGE_DIRECTORY, "", $process['destination']));
				}
				else if ($type == 'project')
				{
					$source = str_replace(DS, '/', DS . 'img' . DS . 'projects' . DS . str_replace(PROJECT_IMAGE_DIRECTORY, "", $process['destination']));
				}
						
				$imageVersions[] = array(
					'version' => $process['version'],
					'source' => $source									 
				);
					
			}//end if big enough					
		}//end foreach
			
		//delete original transferred file	
		if (isset($transferedFile) && (is_file($transferedFile) == TRUE))
		{					
		    while(is_file($transferedFile) == TRUE)
        	{
            	chmod($transferedFile, 0666);
            	unlink($transferedFile);
        	}	
		}
					
		//save the version records
		if ($type == 'user'){
			$imageData = array(
				'User' => array("id" => $typeId),
				'Image' => array(
					'user_id' => $typeId //source user id 
				),
				'ImageVersion' => $imageVersions
			);
		} else if ($type == 'project') {
			
			if (empty($userId)) $userId = CakeSession::read('Auth.User');
						
			$imageData = array(
				'Project' => array("id" => $typeId),
				'Image' => array(
					'user_id' => $userId //source user id		 
				),
				'ImageVersion' => $imageVersions
			);				
			
			
		}
		//debug($imageData);	
		
		if ($this->add($imageData)){
					
			$imageId = $this->getLastInsertId();
			if (!empty($order)){					
				if ($type == 'user'){
					$joinId = $this->ImagesUser->getLastInsertId();	
					if(!empty($joinId))	$this->ImagesUser->updateField($joinId, "order", $order);						
				} else if ($type == 'project'){
					$joinId = $this->ImagesProject->getLastInsertId();	
					if(!empty($joinId))	$this->ImagesProject->updateField($joinId, "order", $order);						
				}
			}
					
			return $imageId;
			
		} else {	
			//debug($image);
			//die('no add');
			return false;			
		} 		
				
	}//end upload
	
	
	//delete is used by Cake
	function deleteImage($imageId, $type = 'user', $order = null){
		
		if (empty($imageId))	return false;
		
		$image = $this->find("first", array(
			"conditions" => array("id" => $imageId),
			"contain" => array("ImageVersion", "Project", "User")
		));
		
		if (empty($image))	return false;
		
		$avatar = false;
		if (isset($image['ImageVersion'])){			
			foreach ($image['ImageVersion'] as $imageVersion){
				$source = $imageVersion['source'];
				if (preg_match("|^/img/|i", $source)){
					
					$source = WWW_ROOT . substr($source, 1);
					//$this->log($source, LOG_DEBUG);
					unlink($source);
				}
				
				if ($imageVersion['version'] == 'avatar'){
					$avatar = true;
				}
			}
		}//end if
		
		if (!empty($type)){
			
			if ($type == 'user'){		
				$model = "User";
				$joinModel = "ImagesUser";				
				$record = $this->$joinModel->find("first", array("conditions" => array($joinModel . ".image_id" => $imageId)));				
				$modelId = $record[$joinModel]['user_id'];		
				
				//remove avatar
				if ($avatar){
					$this->User->updateField($modelId, "avatar", null, false, true);//the second true forced a cache update							
				}
				
			} else {		
				$model = "Project";					
				$joinModel = "ImagesProject";
				$record = $this->$joinModel->find("first", array("conditions" => array($joinModel . ".image_id" => $imageId)));				
				$modelId = $record[$joinModel]['project_id'];				
			}				
		}
		
		$this->delete($imageId);
		
		if (!empty($type)){

			//update order				
			if(!empty($order)){					
				$record = $this->$joinModel->find("first", array("conditions" => array(
						$joinModel . "." . $type . "_id" => $modelId, 
						$joinModel . ".order" => ($order + 1)
					)
				));	

				if (!empty($record))	$this->updateOrder($record[$joinModel]['image_id'], $order, $type);					
			} 
			
			//update count
			$count = $this->$joinModel->find("count", array("conditions" => array(
					$joinModel . "." . $model . "_id" => $modelId
				)
			));	
			
			//debug($modelId . " - " . $model . ".image_count" . " - " . $count);
			$this->$model->updateField($modelId, "image_count", $count);
			CakeSession::write('Auth.User.image_count', $count);
			
			
		}//end if type
		
	}//end deleteImage
	
	//updates the order of an image
	//type = 'user' or 'project'
	function updateOrder($id, $order, $type){
		
		//debug($id . " - " . $order . " - " . $type);
		
		
		if (empty($id) || empty($order) || empty($type))	return false;
		if (($type != 'user') && ($type != 'project'))	return false;
			
		if ($type == 'user')
			$model = "ImagesUser";
		else
			$model = "ImagesProject";
			
				
		$record = $this->$model->find("first", array("conditions" => array($model . ".image_id" => $id)));
		
		if (empty($record))	return false;	
		$typeKey = $type . "_id";
		$typeId = $record[$model][$typeKey];
		$oldOrder = $record[$model]['order'];
			
		if ($oldOrder == $order)	return true;
					
		$records = $this->$model->find("all", array("conditions" => array($model . "." . $type . "_id" => $typeId)));

		$max = count($records);
		if ($order < 1)	$order = 1;			
		if ($order > $max)	$order = $max;
											
		foreach ($records as $record){
				
			$currentOrder = $record[$model]['order'];		
			if ($record[$model]['image_id'] == $id){					
				$newOrder = $order;
			} else {					
				if ($order < $oldOrder){						
					if (($currentOrder >= $order) && ($currentOrder <= $oldOrder)){
						$newOrder = $currentOrder + 1;							
					} else
						$newOrder = $currentOrder;											
				} else {
					if (($currentOrder <= $order) && ($currentOrder >= $oldOrder)){
						$newOrder = $currentOrder - 1;							
					} else
						$newOrder = $currentOrder;																		
				}//end if new order is greater than the old
			} 
				
			if ($newOrder != $currentOrder){
				//debug($record[$model]['image_id'] . " - " . $currentOrder . " to " . $newOrder);
				$this->$model->updateField($record[$model]['id'], "order", $newOrder);
			}
				
		}//end foreach	
		
		return true;
		
	}//end updateOrder

	
	function generateDestination($directory, $version, $base = ""){
		
		if ((empty($directory)) || (empty($version)))	return false;
		
		$createDirectory = true; //recursively create missing directories
		$createDirectoryMode = 0755;

		$Folder = new Folder($directory, $createDirectory, $createDirectoryMode);
		if (!$Folder->pwd()) {
			$message  = "GeneratorBehavior::generateVersion - Directory `{$directory}` ";
				$message .= "could not be created or is not writable. ";
				$message .= "Please check the permissions.";
				trigger_error($message, E_USER_WARNING);
				$result = false;
				continue;
		}
		
		if (strlen($base) > 0)	$base .= ".";
		
		$filename =  $base . '1_' . $version . '.jpg';
				
		$counter = 2;
		while ( file_exists($directory . $filename)  && ($counter < 1000) ) {
			$filename =  $base . $counter . '_' . $version . '.jpg';
			$counter++;
		}
		
		return $directory . $filename;
		
	}//end generateImageFilename	
	

	//check supplied image against rules provided
	//can check mime type and dimensions
	function checkImage($data, $rules = array(), $fieldName = 'file'){
		
		
		$filename = "";
		if ((isset($data[$fieldName]['tmp_name']))&&(is_array($data[$fieldName]))){
			$filename = $data[$fieldName]['tmp_name'];	
		} else if (isset($data[$fieldName])){		
			if (is_string($data[$fieldName])){
				$filename = $data[$fieldName];
			}
		}	
		if ((isset($data['url']))&&(empty($filename))){
			$filename = $data['url'];
		}
		
		
		if (empty($filename))	return false;	
							
		$imageData =  @getimagesize($filename);				
		if (empty($imageData))	return false;	
		list($width, $height) = $imageData;
		
		if (empty($rules))	return true;
		
		$passed = false;
		if (isset($rules['mime'])){

			foreach($rules['mime'] as $acceptableType){
				if (isset($imageData['mime'])){
					if ($acceptableType == $imageData['mime']){
						$passed = true;	
					}
				}
			}
		} else {
			$passed = true;	
		}
		
		//debug($imageData);
		//debug($passed);
		
		if (! $passed)	return $passed;
		
		if (isset($rules['height'])){				 
			foreach($rules['height'] as $operator => $dimension){
				if($operator == "=")	$operator = "==";
				//debug('if (!('. $height . ' ' . $operator . ' ' . $dimension . '))	return false;');
				eval ('if (!('. $height . ' ' . $operator . ' ' . $dimension . '))	$passed = false;');
			}
		}
		if (! $passed)	return $passed;		
		
		if (isset($rules['width'])){				 
			foreach($rules['width'] as $operator => $dimension){
				if($operator == "=")	$operator = "==";
				//debug('if (!('. $width . ' ' . $operator . ' ' . $dimension . '))	return false;');
				eval ('if (!('. $width . ' ' . $operator . ' ' . $dimension . '))	 $passed = false;');
			}
		}		
		
		return $passed;

	}//end checkDimensions
	
	//expects $data array containing a non-empty $data['ImageVersion']
	//requires $data['Project']['id'] or $data['User']['id'] be set
	//if $data['Project']['id'] is set, will associate image with that project
	//if $data['User']['id'] is set, will associate image with that user
	//if $data['ad'] is set, will create ad and and associate image with ad	
	
	function add($data){
		
		if ( ((empty($data['Image'])) || (empty($data['ImageVersion']))) || 
			((empty($data['Project']['id'])) && (empty($data['User']['id']))) ){
			return false;
		}	
		
		//remove any versions with an empty source or a source that alreadys exists for that user or project
		$usedSources = array();
		
		if (!empty($data['Project']['id'])){
			
			$record = $this->Project->find("first", array(
					'fields' => array('Project.id', 'Project.image_count'),
					'conditions' => array('Project.id' => $data['Project']['id']),
					'contain' => array(
						'Image.ImageVersion' => array(
							"limit" => false,
							"fields" => array('id', 'version', 'source')
						)
					)			
				)
			);					
			
		} else if (!empty($data['User']['id'])){
			
			$record = $this->User->find("first", array(
					'fields' => array('User.id', 'User.image_count'),
					'conditions' => array('User.id' => $data['User']['id']),
					'contain' => array(
						'Image.ImageVersion' => array(
							"limit" => false,
							"fields" => array('id', 'version', 'source')
						)
					)			
				)
			);			
			
			
		}//end if user id
		

		
		$imageCount = 0;
		if (!empty($record['Image'])){			
			$imageCount = count($record['Image']);
			foreach ($record['Image'] as $image){
				foreach($image['ImageVersion'] as $imageVersion){
					$usedSources[] = $imageVersion['source'];
				}		
			}
		}//end if			
		
		$used = false;
		if (!empty($usedSources)){			
			//debug($usedSources);		
			foreach ($data['ImageVersion'] as $key => $imageVersion){
				if (in_array($imageVersion['source'], $usedSources)){
					$used = true;
					break;
				}
			}//end foreach		
		}		
		
		//if there were repeats submitted, return false
		if ($used){
			//debug('used');
			return false;
		}
		
		//create image and image versions
		$imageData['Image'] = $data['Image'];
		$imageData['ImageVersion'] = $data['ImageVersion'];
		if (isset($data['Ad']))	$imageData['Ad'] = $data['Ad'];
		$this->create(false);
		if (! $this->saveAll($imageData) ){ 
			//foreach($this->validationErrors as $error)	debug($error);
			return false;
		}
		
		$imageId = $this->getLastInsertId();
		
		$usedCount = count($usedSources);
		if (!empty($data['Project']['id'])){
		
			$imagesProjectData['ImagesProject']['image_id'] = $imageId;
			$imagesProjectData['ImagesProject']['project_id'] = $data['Project']['id'];
			$imagesProjectData['ImagesProject']['order'] = $usedCount + 1;
			$this->Project->ImagesProject->insert($imagesProjectData);
			
			//update count
			$this->Project->updateField($data['Project']['id'], 'image_count', $imageCount + 1);
			
		} else if (!empty($data['User']['id'])){
			
			$imagesUserData['ImagesUser']['image_id'] = $imageId;
			$imagesUserData['ImagesUser']['user_id'] = $data['User']['id'];
			$imagesUserData['ImagesUser']['order'] = $usedCount + 1;			
			$this->User->ImagesUser->insert($imagesUserData);
			
			//update count
			$this->User->updateField($data['User']['id'], 'image_count', $imageCount + 1);			
			
		}		
		
		return true;
		
		/*
		$imageData['Image'] = $data['Image'];
		$image = $this->insert($imageData);
		if (empty($image['Image']['id']))	return false;
		
		//create image versions
		foreach($data['ImageVersion'] as $key => $imageVersion){		
			$data['ImageVersion'][$key]['image_id'] = $image['Image']['id'];	
		}//end foreach		
		$imageVersionData['ImageVersion'] = $data['ImageVersion'];
		$imageVersion = $thisVersion->saveAll($imageVersionData);	
		*/

	}//end function add	

}//end image


?>