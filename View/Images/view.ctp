<script type="text/javascript">

$(function() {	
	


	
});

</script>

<style>
label {width: 100px;}


.image {width:700px;  padding: 10px 2px 10px 2px; text-align: center; background-color: #f1f0f0; margin-bottom: 20px;}

.image .info {margin: 20px 100px 0px 100px; padding-top: 10px; border-top: 1px solid #cccccc;}
.title, .flag {margin-bottom: 10px;}

</style>

<?

if ($imageType == 'user'){
	echo $this->element('breadcrumbs', array('breadcrumbs' => array(
		$this->Html->link('Thoughtmates &gt;', '/thoughtmates', array('escape'=>false)),																	
		$this->Html->link($user['User']['display_name'] . ' &gt;', '/users/view/' . $user['User']['username'], array('escape'=>false)),
		"Images"
	))); 
	
	$header = "<a href = '/users/view/" . $user['User']['username'] . "' class = 'normal' style = 'float: right; margin-right: 40px;'>&lt; go back to full profile</a>" . $header;
	
?>

<div class="user" id="userHeading">
	<table cellspacing="0" cellpadding="0">
		<tbody><tr valign="middle">
			<td class="userPictures medium" >          
<?

echo $this->element('images', array(
	'containerId' => 'userHeading', 
	'imageSize' => 'medium', 
	'userId' => $user['User']['id'], 
	'class' => 'rotateRight'
)); 

?>                  		
			</td>            
			<td class="userText">     
				<h1><?= $user['User']['display_name'] ?></h1>
                <p id = 'tagline'><?= $user['User']['tagline'] ?></p>             
			</td>
		</tr>
	</tbody></table>   
    <img src = "/img/ray_header.gif" /> 	
</div>

<?

} else {
	
	echo $this->element('breadcrumbs', array('breadcrumbs' => array(
		$this->Html->link('Explore &gt;', '/explore', array('escape'=>false)),																	
		$this->Html->link('Thoughts &gt;', '/thoughts', array('escape'=>false)),
		$this->Html->link($thought['Thought']['name'] . ' &gt;', '/thoughts/view/' . urlencode($thought['Thought']['name']) , array('escape'=>false)),
		"Images"		
	)));
	
	$header = "<a href = '/thoughts/view/" . urlencode($thought['Thought']['name']) . "' class = 'normal' style = 'float: right; margin-right: 40px;'>&lt; go back to full thought</a>" . $header;

?>

<div id = 'primaryThought' class = 'primaryThought'>
	<table cellpadding = '0' cellspacing = '0'>
		<tr valign='middle'>
			<td class = 'primaryThoughtImages medium'>
                <?
				
$imageOptions = array('containerId' => 'primaryThought', 'imageSize' => 'medium', 'thoughtId' => $thought['Thought']['id'], 'class' => 'rotateRight');

echo $this->element('images', $imageOptions); 

?>        				
			</td>            
			<td class = 'primaryThoughtText'>          
				<div class="slideContainer" id="thoughtAliases">
					<div class="currentSlide"><h1><?= $thought['Thought']['name'] ?></h1></div>
				</div>            
			</td>
		</tr>
	</table> 
    <img src = "/img/ray_header.gif" />    	
</div><!-- end primaryThought -->

<?

}

?>
<h3 class = 'swoosh'><?= $header ?></h3>

<?
//debug($images);

$max = count($images);
echo $this->Flash->show();

if (empty($images)){
	
	echo $this->Flash->show("There are images to show.");
	
} else {
					
	
	//debug($images);
	
	
	foreach($images as $key => $image){
		
		$source = "";

		foreach ($image['ImageVersion'] as $imageVersion)	if ($imageVersion['version'] == 'large')	$source = $imageVersion['source'];		
		if (empty($source)){
			foreach ($image['ImageVersion'] as $imageVersion)	if ($imageVersion['version'] == 'medium_large')	$source = $imageVersion['source'];			
		}
		if (empty($source)){
			foreach ($image['ImageVersion'] as $imageVersion)	if ($imageVersion['version'] == 'medium')	$source = $imageVersion['source'];					
			if (!empty($source))	$source = str_replace("250_AA250", "500_AA500", $source);	
		}		


		echo "<div id = 'imageContainer" . $image['id'] . "' class = 'imageContainer'>
	<div class = 'image round'>
		<img id = 'image". $image['id'] . "' class = 'areaSelect' src = '" . $source . "' title = \"" . $image['title'] . "\" />
		<div class = 'info'>";
		
	if (!empty($image['title']))	echo "<p class = 'title'>" . $image['title'] . "</p>";	
	echo "<p class = 'flag'><img src =  '/img/flag.png' style = 'vertical-align:middle;' />&nbsp;&nbsp;<a href = '#' onClick = \"flagImage('" . $image['id'] . "'); return false;\">Flag this image as inaccurate or inappropriate</a></p>\n";

		
echo "		</div>\n	</div>\n</div>\n";


	}//end foreach
				
}//end if


?>
    



