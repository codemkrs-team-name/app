<?php
class ProjectsController extends AppController {
	
	
	public function index($type=null)
	{
		$title = "projects";
		$projectType = "";
		if ($type == 'artwork')	$projectType = 'art';
		if ($type == 'websites') $projectType = 'site';		
			
		if (!empty($projectType))
		{
			$title = $type;
			$conditions = array();
			if ($projectType == 'art')
			{
				$conditions = array("type" => $projectType);
			}
			else
			{
				$conditions = array("type NOT IN ('art', 'non-web job')");		
			}
		}
		else
		{
			$conditions = array("type <>" => "non-web job");
		}
		
		$tagged = "";
		$joins = array();
		if (!empty($this->request->params['named']['tagged']))
		{
			$tagged = Sanitize::clean(urldecode($this->request->params['named']['tagged']));
			$conditions = array();
			$joins = array(
				array(
					'table' => 'projects_tags',
        			'alias' => 'ProjectsTag',
        			'conditions' => array(
            			'ProjectsTag.project_id = Project.id',
        			)
				),
				array(
					'table' => 'tags',
        			'alias' => 'Tag',
        			'conditions' => array(
            			'Tag.id = ProjectsTag.tag_id',
						'Tag.name' => $tagged
        			)
				),				
    		);

		}
		
		
		$records = $this->Project->find("all", array(
			"conditions" => $conditions,
			"contain" => array("Tag.name", "Tag.count"),
			"joins" => $joins
		));		
		
				
		foreach ($records as $key => $record)
		{
			$excerpt = strip_tags($record['Project']['description']);
			if (strlen($excerpt) >= 240)	$excerpt = substr($excerpt, 0, 237) . "...";			
			$record['Project']['description'] = $excerpt;
			
			if (!empty($record['Project']['start_date']))
			{
				$dateTime = new DateTime($record['Project']['start_date']);
				$record['Project']['start_date'] = date_format($dateTime, 'F Y');
			}
			if (!empty($record['Project']['end_date']))
			{
				$dateTime = new DateTime($record['Project']['end_date']);
				$record['Project']['end_date'] = date_format($dateTime, 'F Y');
			}
			
			$records[$key] = $record;							
		}
	
		$this->set('title', $title);
		$this->set('tagged', $tagged);
		$this->set('title_for_layout', $title);
		$this->set('type', $type);
		$this->set('records', $records);
	}
	
	public function resume($type=null)
	{
		
		$records = $this->Project->find("all", array(
			"conditions" => array("Project.type IN ('job', 'non-web job')"),
			"contain" => array("Tag.name", "Tag.count"),
		));
			
		foreach ($records as $key => $record)
		{
			
	
			if (!empty($record['Project']['start_date']))
			{
				$dateTime = new DateTime($record['Project']['start_date']);
				$record['Project']['start_date'] = date_format($dateTime, 'F Y');
			}
			if (!empty($record['Project']['end_date']))
			{
				$dateTime = new DateTime($record['Project']['end_date']);
				$record['Project']['end_date'] = date_format($dateTime, 'F Y');
			}
			
			$records[$key] = $record;				
		}			
			
		$this->set('title_for_layout', "Josh Anderson's resume");
		$this->set('records', $records);
		$this->set('tags', $this->Project->Tag->find("all", array("conditions" => array('Tag.count >' => 0))) );
		
	}	
	
	
	public function view($name=null)
	{
		$name = urldecode($name);
		$record = $this->Project->find("all", array(
			"conditions" => array('name' => $name),
			"contain" => array("Image", "Image.ImageVersion", "Tag")
		));	
		
		if (empty($record))	 throw new NotFoundException(__("We couldn't find that project."));
		
		$record = $record[0];
		$imagesRevised = false;
		if (!empty($record['Image'])){
			$images = $record['Image'];
			unset($record['Image']);
					
			$imagesRevised = array();
			foreach ($images as $image)
			{
				$imageRevised = array();
				if (!empty($image['ImageVersion']))
				{				
					$imageRevised['title'] = $image['title'];
					foreach($image['ImageVersion'] as $imageVersion)
					{
						if($imageVersion['version'] == 'huge')	$imageRevised['huge'] = $imageVersion['source'];	
						if($imageVersion['version'] == 'large')	$imageRevised['large'] = $imageVersion['source'];	
						if($imageVersion['version'] == 'medium_large')	$imageRevised['medium_large'] = $imageVersion['source'];	
						if($imageVersion['version'] == 'small')	$imageRevised['small'] = $imageVersion['source'];	
					}
	
					if (!empty($imageRevised['huge']))	
						$imageRevised['big'] = $imageRevised['huge'];
					else if (!empty($imageRevised['large']))	
						$imageRevised['big'] = $imageRevised['large'];
					else if (!empty($imageRevised['medium_large']))	
						$imageRevised['big'] = $imageRevised['medium_large'];
					
					if (!empty($imageRevised['big']))	$imagesRevised[] = $imageRevised;									
				}
			}				
		}	
		
		if (!empty($record['Project']['start_date']))
		{
			$dateTime = new DateTime($record['Project']['start_date']);
			$record['Project']['start_date'] = date_format($dateTime, 'F Y');
		}
		if (!empty($record['Project']['end_date']))
		{
			$dateTime = new DateTime($record['Project']['end_date']);
			$record['Project']['end_date'] = date_format($dateTime, 'F Y');
		}
							
		$this->set('title', $name);
		$this->set('record', $record);
		$this->set('images', $imagesRevised);
	}	
	
	

	public function edit($id)
	{
		//$this->Project->cacheQueries = false;
		
        if ($this->request->is('post') || $this->request->is('put')) {
			
			//debug($this->request->data);
			
			if ($this->Project->save($this->request->data['Project']))
			{
				$this->flash("Project saved", "success");	
			}
			
			$submittedTags = $this->request->data['Tag'];
			
			foreach ($submittedTags as $submittedTagId => $checked)
			{
				
				$projectsTag = $this->Project->ProjectsTag->find("first", array('conditions' => array(
					'project_id' => $id, 
					'tag_id' => $submittedTagId
					))
				);
				
				$tag = $this->Project->Tag->findById($submittedTagId);
				$tagCount = $tag['Tag']['count'];
				//debug($projectsTag);
				
				if ($checked  && empty($projectsTag)) //insert
				{
					$this->Project->ProjectsTag->insert(array(
						'project_id' => $id, 
						'tag_id' => $submittedTagId
					));
					
					$tagCount++;
				}
				else if ((! $checked) && (!empty($projectsTag))) //delete
				{
					$this->Project->ProjectsTag->delete($projectsTag['ProjectsTag']['id']);
					$tagCount--;
				}
					
				if ($tag['Tag']['count'] != $tagCount)
				{
					$tagCount = $this->Project->ProjectsTag->find("count", array("conditions"=>array("tag_id" => $submittedTagId)));
					
					$this->Project->Tag->updateField($submittedTagId, "count", $tagCount);
				}
				
			}
			
        }//end if posted		
		
		
		$record = $this->Project->find("all", array(
			"conditions" => array('id' => $id),
			"contain" => array("Image", "Image.ImageVersion", "Tag")
		));	
		
		if (empty($record))	 throw new NotFoundException(__("We couldn't find that project."));
		
		$record = $record[0];		
		$tags = $this->Project->Tag->find("all");		
		
	
		
					
		$this->set('title', "edit " . $record['Project']['name']);
		$this->set('record', $record);
		$this->set('tags', $tags);
	}	

}//end class
?>