<?
//debug($project);

//requries: $project['id'], $project['name'], $counter
//optional: $type (defaults to list), $description, $size (defaults to 'thumbnail'), $imageLimit (defaults to 5), $class
if ((isset($project['id']))&&(isset($project['name']))&&(isset($counter))){

	if(!isset($type)) $type = 'list';
	if(!isset($size)) $size = 'thumbnail';
	if(!isset($imageLimit)) $imageLimit = 5;
	if(!isset($class)) $class = "";
	if(!isset($before))	$before = "";
	if(!isset($after))	$after = "";
	
	if(!isset($style)) 
		$style = "";
	else
		$style = "style = '$style'";
					
	$imageSize = $size;	
	$containerId = $type . "project" . $counter;
	
		
		
	$meta = "";
	$meta = "<td class = 'quiet small'>" . $meta . "</td>";

?>	
		<div id = '<?= $containerId ?>' class = '<?= $type ?> project <?= $size . ' ' . $class ?>' <?=$style?>>
        	<?= $before ?>
			<table cellpadding = '0' cellspacing = '0' border = '0'>
				<tr valign='middle'>
					<td class = 'projectText'>
                    	<h2>
<? 
echo $this->Html->link($project['name'], '/projects/view/' . urlencode($project['name']), array('escape' => false));

?></h2>
                 		<table>
                        	<tr>
                            	<?= $meta ?>
                            </tr>
                        </table>
                	</td>            
					<td class = 'projectImages' valign = 'middle'>
                    	<? //echo $this->element('images', array('containerId' => $containerId, 'imageSize' => $imageSize, 'projectId' => $project['id'], 'limit' => $imageLimit, 'class' => $size, 'imageLink' => '/projects/view/' . urlencode($project['name'])  )); ?>
                	</td>            
				</tr>
			</table>  
             <?= $after ?> 	
    	</div><!-- end project -->     
         
<?        
}//end if necessary parameters passed
?>
