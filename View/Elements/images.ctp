<?
if ((!empty($projectId))||(!empty($userId))){
	
	
	if (!isset($limit)) $limit = 10;
	if (!empty($projectId))
		$images = $this->requestAction('/images/get/project/' . $projectId . '/' . $limit);
	else if (!empty($userId))
		$images = $this->requestAction('/images/get/user/' . $userId . '/' . $limit);
		
	if (!isset($imageSize))	$imageSize = 'small';  //image sizes available large: 700px, medium: 250px, small: 120px, thumbnail: 80px	
			
    $containerId = uniqid();

	if (is_array($images)){
		
		//debug($images);
		$count = count($images);
		if ($count > 0){
			
	
			echo "<div id = 'slides" . $containerId . "' class='slides $imageSize'><table class = 'layout'>
  <tr>
    <td style = 'width:30px; text-align:left; vertical-align:middle;' >";
    
		if ($count > 1){
			echo "		<a href = '#previous' class = 'slideLeft'><span class='ico ico-arrow-left'>&nbsp;</span></a>";
		}  
             
   	echo "</td>
    <td>";
	
			echo "		<div id='slideContainer" . $containerId . "' class = 'slideContainer shadow rotateRight'>\n";
	
			$counter = 0;		
			//debug($images);
            
			foreach ($images as $image){
						
				$image['url'] = "";
				if (!empty($userId))
					$image[$imageSize] = '/img/user_not_found_' . $imageSize . '.gif';				
				else
					$image[$imageSize] = '/img/image_not_found_' . $imageSize . '.gif';
					
				if (isset($image['ImageVersion'])){
					foreach ($image['ImageVersion'] as $imageVersion){
						if ($imageVersion['version'] == $imageSize){
							$image[$imageSize] = $imageVersion['source'];
							break;
						}
					}
				}
				
				$class = "slide";
				if ($counter == 0) $class = "currentSlide";
					
				if (empty($image['url']))
				{
					if (!empty($imageLink))	$image['url'] = $imageLink;
				}
				echo "					<div class = '$class'><a href = '" . $image['url'] . "'><img alt = '" . $image['title'] . "' src = '" . $image[$imageSize] . "' align='middle' /></a></div>\n";
				$counter++;
			}//end foreach
	
			echo "	</div>\n";
			
			echo "	</td>
    <td style = 'width:30px; text-align:right; vertical-align:middle;' >";
    
			if ($count > 1){
				echo "		<a href = '#next' class = 'slideRight' ><span class='ico ico-arrow-right'>&nbsp;</span></a>";
			}     
    
    echo "</td>
  </tr>
</table></div>";
			
			
			
		}//end if images exist		
	}//end if
}//end if
?>
