<?php

/* takes: 
full path / filename of new cropped image
full path / filename of source image/
width of selection,
height of selection,
x coordinate of selection to start with (continues to x + width),
y coordinate of selection to start with (continues to y + height),
scale value of cropped image (1 will make it the width / height given)
*/
function cropImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale = 1){
	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
	$imageType = image_type_to_mime_type($imageType);
	
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	switch($imageType) {
		case "image/gif":
			$source=imagecreatefromgif($image); 
			break;
	    case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
			$source=imagecreatefromjpeg($image); 
			break;
	    case "image/png":
		case "image/x-png":
			$source=imagecreatefrompng($image); 
			break;
  	}
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
	switch($imageType) {
		case "image/gif":
	  		imagegif($newImage,$thumb_image_name); 
			break;
      	case "image/pjpeg":
		case "image/jpeg":
		case "image/jpg":
	  		imagejpeg($newImage,$thumb_image_name,90); 
			break;
		case "image/png":
		case "image/x-png":
			imagepng($newImage,$thumb_image_name);  
			break;
    }
	chmod($thumb_image_name, 0777);
	return $thumb_image_name;
}//end cropImage


class ImagesController extends AppController {
	
	

	var $name = 'Images';
	var $uses = array('Image', 'Project', 'User'); //defines which models should be available to the controller
	

	function view($type = 'project', $id){

		
		if (empty($id)){			
			$this->flash("A user or project id is required to view images.", "error");
			$this->redirect("/");
		}
		
		
		$data = $this->User->find("first", array(
				'fields' => array('User.id', 'User.username', 'User.display_name', 'User.tagline'),
				'conditions' => array('User.id' => $id),
				'contain' => array(
					'Image' => array('limit' => false, 'order' => 'ImagesUser.order ASC', 'conditions' => array('Image.flagged < 1')),
					'Image.ImageVersion' => array('version', 'source')
				)
			));		
	

			
			$data = $this->Project->find("first", array(
				'fields' => array('Project.id', 'Project.name'),
				'conditions' => array('Project.id' => $id),
				'contain' => array(
					'Image' => array('limit' => false, 'order' => 'ImagesProject.order ASC', 'conditions' => array('Image.flagged < 1')),
					'Image.ImageVersion' => array('version', 'source')
					)
			));		
			
			$header = "Images of " . $data['Project']['name'];
			
			$project['Project'] = $data['Project'];
			$this->set('project', $project);
		
	
		$images = $data['Image'];
				
		//$this->set('type', $type);
		//$this->set('images', $images);
		
		$this->set(array(
			'imageType' => $type, //throw an imgController missing exception when you use 'type', for some reason
			'images' => $images,
			'header' => $header,
			'title' => $header
		));				
		
		
	}//end view
	

	function edit($type = 'user'){
		
		$user['User'] = $this->Session->read('Auth.User');		
		
		$type = strtolower($type);
		
		$imageId = null;
		if (isset($this->request->params['named']['imageId']))	$imageId = $this->request->params['named']['imageId'];
		$projectId = null;
		if (isset($this->request->params['named']['projectId'])){
			
			$projectId = $this->request->params['named']['projectId'];			
			$project = $this->Project->findById($projectId);
			
			if (empty($project)){
				$this->flash("The project id '$projectId' is invalid.");
				$this->redirect('/');					
			} 
			
			$this->set('projectId', $projectId);
			$this->set('project', $project);
		}

		$header = "Add or edit project images";
			
		$mode = "upload";		
	
		if (!empty($imageId)){
			$header = "Edit this image";
			$mode = "edit";
			$this->set('imageId', $imageId);
		}

		if (($type == 'project') && (!empty($projectId))){
			
			$data = $this->Project->find("first", array(
				'fields' => array('Project.id'),
				'conditions' => array('Project.id' => $projectId),
				'contain' => array(
					'Image' => array('limit' => false, 'order' => 'ImagesProject.order ASC'),
					'Image.ImageVersion' => array('version', 'source')
					)
			));	
			
		} else {		
			$this->flash("Editing and uploading pictures is not possible with the parameters provided.");
			$this->redirect('/');			
		}
							
		$images = $data['Image'];		
		
		
		//if the order is missing or incorrect, correct it
		$expectedOrder = 1;
		//debug($images);
		foreach ($images as $key => $image){
			
	
			$order = $image['ImagesProject']['order'];
			if ($order != $expectedOrder){
				$this->Image->ImagesProject->updateField($image['ImagesProject']['id'], "order", $expectedOrder);
				$images[$key]['ImagesProject']['order'] = $expectedOrder;
			}								
									
			$expectedOrder++;
		}//end foreach
		
		$this->set(array(
			'imageType' => $type, //throw an imgController missing exception when you use 'type', for some reason
			'user' => $user,
			'mode' => $mode,
			'images' => $images,
			'header' => $header			
		));		
		
	}//end edit
	
	function upload(){

		//$this->redirectGuests();
		$this->autoRender = false;
		$user['User'] = $this->Session->read('Auth.User');

		$type = $this->request->data['type'];
		$redirectUrl = '/images/edit/' . $type;
			
		if (isset($this->request->data['Project']['id']))	$projectId = $this->request->data['Project']['id'];		
		if (!empty($projectId))	$redirectUrl .= "/projectId:" . $projectId;
						
		if (($type != 'user') && ($type != 'project'))
		{
			$this->flash("The type parameter given ($type) is invalid.");
			$this->redirect('/images/edit/');	
		}	
		 
		if ((!empty($this->request->data['Image']['file'])) || (!empty($this->request->data['Image']['url']))){
					
			$order = null;
			if (!empty($this->request->data['order']))	$order = $this->request->data['order'];			
			$image = $this->request->data['Image'];
			//debug($image);
			//debug($type);
			if ($type == 'project')
				$typeId = $projectId;
			else
				$typeId = $user['User']['id'];
				
			
				
			$imageId = $this->Image->upload($image, $type, $typeId, array('order' => $order) );
			
			if (!empty($imageId))
			{
				//redirect on success
				$this->redirect($redirectUrl . "/imageId:" . $imageId);				
			}
			else
			{			
				if (!empty($this->Image->validationErrors)){
					foreach($this->Image->validationErrors as $errorType){
						foreach($errorType as $error){
							$this->flash($error, 'error');
						}
					}
				} else {
					$this->flash("There was a problem saving your image. Please try again later.", "error");
				}	
					
				$this->redirect($redirectUrl);				
			}

		}//end if file not empty
			
	}//end upload

	
	//accessed via AJAX
	function update(){
	
	
$this->autoRender = false;
		$user['User'] = $this->Session->read('Auth.User');
	
		if (empty($this->request->data['Image']['id'])){
			$this->flash("Parameters necessary to update this image were missing.  Please try again later.");
		} else {

			$data = $this->request->data;
			
			//generate new cropped image if coordinates present
			if (isset($data['x1']) && isset($data['x2']) && isset($data['y1']) && isset($data['y2'])){
				
				$imageVersion = $this->Image->ImageVersion->find("first", array(
					"conditions" => array("version" => 'medium_large', 'image_id' => $data['Image']['id'])
				));
				
				if (!empty($imageVersion)){//if we can find the image we are cropping from, proceed
		
					$source = $imageVersion['ImageVersion']['source'];
					if (preg_match("|^/img/|i", $source)){
					
						$source = WWW_ROOT . substr($source, 1);
						$avatarSource = "";

						$cropSizes = array('medium' => 250, 'small' => 120, 'thumbnail' => 80);
						if (!empty($data['avatar']))	$cropSizes['avatar'] = 40;
				
						foreach($cropSizes as $key => $cropSize){
							
							$x1 = $data['x1'];
							$x2 = $data['x2'];
							$y1 = $data['y1'];
							$y2 = $data['y2'];
													
							$selectionSize = $x2 - $x1;
							$scale = $cropSize / $selectionSize;
							//$this->log("scale = $scale selection size = $selectionSize", LOG_DEBUG);
							
							
							if (($scale > 1) && ($key == 'medium')){//enlarge selection for medium images
								
								list($width, $height) = getimagesize($source);							
								$difference = $cropSize - $selectionSize;
								
								$x1 = $x1 - ($difference / 2);
								if ($x1 < 0)	$x1 = 0;
								$x2 = $x1 + $cropSize;
								if ($x2 > $width){
									$x1 = $width - $cropSize;
								}
						
								$y1 = $y1 - ($difference / 2);
								if ($y1 < 0)	$y1 = 0;								
								$y2 = $y1 + $cropSize;
								if ($y2 > $height)	$y1 = $height - $cropSize; 		
								
								//$this->log("x1 = $x1 x2 = $x2 y1 = $y1 y2 = $y2 $cropSize", LOG_DEBUG);
								
								$selectionSize = $cropSize;								
								$scale = 1;
							}//end if 
							
							
							if (($scale <= 1) && ($x1 >= 0) && ($y1 >= 0)){
								
								$cropSource = str_replace('medium_large', $key, $source);
								if ($key == 'avatar'){
									$avatarSource = preg_replace("/\.[0-9]+_/i", "_", $cropSource);
									$cropSource = $avatarSource;
								}
								
								$cropped = cropImage($cropSource, $source, $selectionSize, $selectionSize, $x1, $y1, $scale);							
							}//end if not scaling up	
						}//end foreach
						
						if ((!empty($data['avatar']))&&(!empty($avatarSource))){
	
							$imageVersion = $this->Image->ImageVersion->find("first", array(
								"conditions" => array("version" => 'avatar', 'image_id' => $data['Image']['id'])
							));
							
							$avatarSource = str_replace(WWW_ROOT, "/", $avatarSource);
							if (!empty($imageVersion)){//update		
								//no need to update: the avatar image source won't change
								//$this->Image->ImageVersion->updateField($imageVersion['imageVersion']['id'], "source", $avatarSource);
							} else {//create new
								$this->Image->ImageVersion->insert(array('image_id' => $data['Image']['id'], 'version' => 'avatar', 'source' => $avatarSource));								
							}		
							$this->User->updateField($user['User']['id'], "avatar", $avatarSource, true, true);//the second true forced a cache update
							
						}//end if use as avatar
						
					}//end if local									
				}//end if original version can be found
			}//end if coordinates present
			
			$imageData['Image'] = $data['Image'];		
			$this->Image->update($imageData['Image']['id'], $imageData);			
			if (!empty($this->Image->validationErrors)){
				
				foreach($this->Image->validationErrors as $errorType){
					foreach($errorType as $error){
						$this->flash($error, 'error');
					}
				}
						
			} else {//no validation errors
				
				if ($data['order'] != $data['oldOrder']){
					
					//$this->flash($imageData['Image']['id'] . " - " . $data['order'] . " - " . $data['type']);
					$this->Image->updateOrder($imageData['Image']['id'], $data['order'], $data['type']);
							
				}//end if order updated
				
				$this->flash("The image was updated succesfully.", "success");
			}
			
			
			
			
		}//end if
		
	}//end function update
	
	//accessed via AJAX
	function delete($imageId, $type = 'user', $order = null){

		//$this->redirectGuests();
		$this->autoRender = false;
		$user['User'] = $this->Session->read('Auth.User');
	
		if (empty($imageId)){
			$this->setHeader("500", "Necessary parameters are missing.");
			return false;
		}		
		
		if (($type != 'user') && ($type != 'project'))	return false;
		//$model = "ImagesUser";
		//if ($type == 'project')	$model = "ImagesProject";
		
		$access = array();
		if ($user['User']['type'] == 'admin')	
			$access = true;		
		else {
			$access = $this->Image->find("first", array("conditions" => array("Image.user_id" => $user['User']['id'], "Image.id" => $imageId)));
		}
		
		
		//if user owns the image, let them delete it
		if(!empty($access)){
			$this->Image->deleteImage($imageId, $type, $order);
			$this->flash("Your image was deleted succesfully", "success");
		} else {
			$this->setHeader("500", "Image #$imageId either does not exist, or you don't have the access necessary to delete it.");
			return false;
		}
		
		//debug($user['User']['id']);
		//debug($imagesUser);
		
		
		//$this->render('/Pages/test');
		
		
	}//end function deleteImage
	
	//accessed via AJAX
	function flag($imageId){

		$user['User'] = $this->Session->read('Auth.User');
			
		$image = $this->Image->findById($imageId);
		
		if (empty($image)){
			$this->flash("Image #" . $imageId . " does not exist.");			
		} else {			
			$flagged = $image['Image']['flagged'];
			$flagged = $flagged + 1;
			
			$this->Image->updateField($image['Image']['id'], "flagged", $flagged);		
			$this->flash("You have succesfully flagged this image.  We won't display it publicly again until it can be reviewed.");
		}
		
		
	}//end function flag
	
	function add(){
		$this->autoRender = false;
		/*
		$data = array(
			'User' => array('id' => 3),
			'Image' => array('title' => 'test'),
			'ImageVersion' => array(
				0 => array(
					'source' => 'test large',
					'version' => 'large'
				),
				1 => array(
					'source' => 'test medium',
					'version' => 'medium'
				)
			)				  
		);
		
		$record = $this->Image->add($data);
		
		debug($record);		
		*/
	}

	//get  images
	function get($type, $id, $limit = 100) {
		
		if ((isset($this->params['requested']))&&(!empty($id))&&(!empty($type))){//if parameters given and requested internally	
				
			$imageCount = 0;
		
			if ($type == 'project'){
		
				$records = $this->Project->find("first", array(
						'conditions' => array('Project.id' => $id),
						'contain' => array(
							'Image' => array(
								'limit' => $limit 
							),
							'Image.ImageVersion' => array('version', 'source')
						)
					)
				);		
				
				return $records['Image'];
				
			}
			
		}
		else
		{
			return false;	
		}
	}//end get
	
	function test(){
		
	//	$this->autoRender = false;

	//	$this->User->getAvatar('3');
		
		//$this->Image->deleteImage($imageId, "user", $order);
	
	}//end test

}//end class
?>