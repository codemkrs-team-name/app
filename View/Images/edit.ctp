<link rel="stylesheet" type="text/css" href="/css/jquery/imgareaselect/imgareaselect-default.css" />
<script type="text/javascript" src="/js/jquery.imgareaselect.pack.js"></script>

<script type="text/javascript">

$.extend($.imgAreaSelect, { animate: function (fx) { var start = fx.elem.start, end = fx.elem.end, now = fx.now, curX1 = Math.round(start.x1 + (end.x1 - start.x1) * now), curY1 = Math.round(start.y1 + (end.y1 - start.y1) * now), curX2 = Math.round(start.x2 + (end.x2 - start.x2) * now), curY2 = Math.round(start.y2 + (end.y2 - start.y2) * now); fx.elem.ias.setSelection(curX1, curY1, curX2, curY2); fx.elem.ias.update(); }, prototype: $.extend($.imgAreaSelect.prototype, { animateSelection: function (x1, y1, x2, y2, duration) { var fx = $.extend($('<div/>')[0], { ias: this, start: this.getSelection(), end: { x1: x1, y1: y1, x2: x2, y2: y2 } }); if (!$.imgAreaSelect.fxStepDefault) { $.imgAreaSelect.fxStepDefault = $.fx.step._default; $.fx.step._default = function (fx) { return fx.elem.ias ? $.imgAreaSelect.animate(fx) : $.imgAreaSelect.fxStepDefault(fx); }; } $(fx).animate({ cur: 1 }, duration, 'swing'); } }) }); 
var mode = '<?= $mode ?>';
var uploadLocation = window.location.href;
if (uploadLocation.indexOf('imageId') !== -1)	uploadLocation = uploadLocation.substr(0, uploadLocation.indexOf('imageId'));
//alert(uploadLocation);

//update the order in the other select elements when one is updated
function updateOrder(imageId, order){
	
	var oldOrder = $("#oldOrder" + imageId).val();
	//alert(imageId + " - " + order + " - " + oldOrder);
	
	$("select.imageOrder").each(function(){
		$select = $(this);
		
		var currentOrder = parseInt($select.val());
		
		if ($select.attr('id') != ('ImageOrder' + imageId)){
			
			if (order < oldOrder){						
				if ((currentOrder >= order) && (currentOrder <= oldOrder)){
					newOrder = currentOrder + 1;							
				} else
					newOrder = currentOrder;											
			} else {
				if ((currentOrder <= order) && (currentOrder >= oldOrder)){
					newOrder = currentOrder - 1;							
				} else
					newOrder = currentOrder;																		
			}//end if new order is greater than the old
		
			if (newOrder != currentOrder){
				//alert($select.attr('id') + " - " + newOrder);
				$select.val(newOrder);
			}			
			
		}//end if
	});

}//end updateOrder

var lastDeletedImageOrder = "";
function deleteImage(imageId, type, order){
	
	//optional arguments
	var confirmed = (arguments[3]) ? arguments[3] : false;
	
	
	if (! confirmed){		
		dialog("Are you sure you want to delete this image?", { buttons: { 
			  "No": function() { $(this).dialog("close");},		   
			  "Yes": function() { $(this).dialog("close"); deleteImage(imageId, type, order, true);}
		}});
		return false;
	}
	
	
	
	if (imageId.length < 1){
		dialog("The image id is missing.", {type:'error'});
		return false;
	}		
	
	
	var url = "/images/delete/" + encodeURIComponent(imageId)  + "/" + encodeURIComponent(type)  + "/" + encodeURIComponent(order)  + ".json";

	loading();	
	$.ajax({
   		type: 'GET',
   		url: url,
		dataType: 'json',
   		error: function(data, status) {		
		
			loaded();
			
			if (! data.statusText)	data.statusText = "There was a problem deleting the image.";
						
			if (mode == 'upload')
				dialog(data.statusText, {type:'error'});
			else {
				dialog(data.statusText + "<br/>Click 'Ok' to continue.", { buttons: { 		   
			   		"Ok": function() { $(this).dialog("close"); window.location = uploadLocation;}
				}});
			}

		},
		success: function(json){
			
			loaded();
			
			if (mode != 'upload'){
				dialog("Your image was deleted sucessfully.<br/>Click 'Ok' to continue.", { type: "success", buttons: { 		   
			   		"Ok": function() { $(this).dialog("close"); window.location = uploadLocation;}
				}});
				return;
			}
				
			if (json.flashes)	showFlashes(json.flashes);
			
			lastDeletedImageOrder = parseInt($("#oldOrder" + imageId).val());
			$("#imageContainer" + imageId).remove();			
						
			if (lastDeletedImageOrder.length > 0){
				$("select.imageOrder").each(function(){
													 
					var $select = $(this);
					var imageId = $(this).attr('id');
					imageId = imageId.replace('ImageOrder', '');
							
					var order = parseInt($("#oldOrder" + imageId).val());
					//alert(imageId + " - " + order + " - " + lastDeletedImageOrder);
					if (order == (lastDeletedImageOrder + 1)){				
						updateOrder(imageId, (order - 1));
					}
				});					
			}//end if last order		
		}//end success	
	});

}//end deleteImage

function preview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 80 / selection.width;
    var scaleY = 80 / selection.height;
	
	var $image = $(img);
	var id = img.id.replace("image", "");
	var preview = document.getElementById("preview" + capWords(img.id));
	var $preview = $(preview);
	var width = $image.width();
	var height = $image.height();	
	
	if (img.src != preview.src){
		preview.src = img.src;
		var $previewExplanation = $("#previewExplanation" + id);
		$previewExplanation.html("<strong>Updated thumbnail.</strong><br/>To save, click 'Update Image'.");
	}
	
	

	//alert('#preview' + capWords(img.id));

    $preview.css({
        width: Math.round(scaleX * width),
        height: Math.round(scaleY * height),
        marginLeft: -Math.round(scaleX * selection.x1),
        marginTop: -Math.round(scaleY * selection.y1)
    });

	//previewContainer" . $image['id'] . "
	//<input type='hidden' name='data[type]' value='" . $imageType . "' />
	
	var coordinates = ['x1','x2','y1','y2'];
	
	for (var counter = 0; counter < coordinates.length; counter++){
		
		coordinate = coordinates[counter];
		eval("value = selection." + coordinate + ";\n");
		
		if ($("#image" + id + coordinate).length > 0){
			$("#image" + id + coordinate ).val(value);
		} else {
			$("#previewContainer" + id).after("<input type='hidden' id = 'image" + id + coordinate + "' name='data[" + coordinate + "]' value='" + value + "' />\n");
		}
		
	}//end for

	/*
    $('#x1').val(selection.x1);
    $('#y1').val(selection.y1);
    $('#x2').val(selection.x2);
    $('#y2').val(selection.y2);
    $('#w').val(selection.width);
    $('#h').val(selection.height);   */ 
	
}//end preview

var iasObjects = [];
function editCrop(id){
		

	var $image = $("#image" + id);
	if ($image.length < 1)	return false;
	
	var $link = $("#editCrop" + id);
	var $preview = $("#previewContainer" + id);	
		
	

	var ias = false;
	for (var i = 0; i < iasObjects.length; i++){
		if (iasObjects[i].id == id)	ias = iasObjects[i].instance;			
	}

	if ($image.hasClass('areaSelecting')){//if already activated, deactivate and quit
		$image.removeClass('areaSelecting');
		if (ias)	ias.setOptions({ hide: true });
		$preview.hide();
		$link.html("Edit crop point");
		return;
	} 	

	$link.html("Hide crop point");	
	$image.addClass('areaSelecting');	
	$preview.show('slow');

	var width = $image.width();
	var height = $image.height();	
	var x1start = Math.floor(width / 2);
	var x2start = x1start + 1;
	var y1start = Math.floor(height / 2);
	var y2start = y1start + 1;			
	var x1final = x1start - 60;
	var x2final = x1final + 120;
	var y1final = y1start - 60;
	var y2final = y1final + 120;	
	
	if (!ias){
		ias = $image.imgAreaSelect({ 
			maxWidth: 200, 
			maxHeight: 200, 
			minWidth: 120, 
			minHeight: 120, 
			width: 120,
			height: 120,
			x1: x1start, 
			y1: y1start, 
			x2: x2start, 
			y2: y2start,
			aspectRatio: '1:1',
			handles: true, 
			fadeSpeed: 200, 
			onSelectChange: preview,
			show: true,
			instance: true
		}); 
		
		iasObjects.push({id: id, instance: ias});				
		ias.animateSelection(x1final, y1final, x2final, y2final, 'fast');
	} else {
		ias.setOptions({ show: true });
	}
	
		 
}//end editCrop	


var lastUpdatedImageId = "";
$(function() {
	$("#tabs").tabs();		

	

	$('.ajaxForm').ajaxForm({
		success: function(json){
		
			if (mode != 'upload'){
				dialog("Your image was updated sucessfully.<br/>Click 'Ok' to continue.", { type:'success', buttons: { 		   
			   		"Ok": function() { $(this).dialog("close"); window.location = uploadLocation;}
				}});	
				return;
			} else
				if (json.flashes)	showFlashes(json.flashes);
			
			//update oldOrder to reflect newly set order
			if (lastUpdatedImageId.length > 0){
				$("#oldOrder" + lastUpdatedImageId).val( $('#ImageOrder' + lastUpdatedImageId).val() );
			}			
		}
	});		
	
});
	

	

</script>





<style>
label {width: 100px;}
.ui-tabs-panel {padding-top:20px;}
.ui-tabs-panel p {clear:both; margin-top: 20px;}
.ui-tabs-panel div.input {width: 540px;}

.image {width:500px; min-height: 270px;  padding: 10px; text-align: center; background-color: #f1f0f0; margin:0px; margin-bottom: 20px; display: block; }
.imageMenu {
	margin-top: 20px; 
	padding: 10px; 
	padding-left: 0px; 
	padding-right: 20px; 
	width:160px; 
	float: right; 
	background-color: #f1f0f0;   
	border-radius: 0px 10px 10px 0px;
	-moz-box-shadow:    1px 1px  2px #ccc;
    -webkit-box-shadow: 1px 1px 2px #ccc;
  	box-shadow:         1px 1px 2px #ccc; 
}
.imageMenu label {text-align: left; width: 160px; display: block;}
.imageMenu .input {width: 160px; border-bottom: 1px solid #cccccc;}

div.previewContainer {display: none;}
div.previewExplanation {width: 70px; float: right; line-height: 15px;}
div.preview {float: left; margin-top: 5px; width: 80px; height: 80px; overflow: hidden;}

</style>

<?
$breadcrumbs = array('projects' => '/projects');
if ($project['Project']['type'] == 'art')
	$breadcrumbs['artwork'] = '/projects/artwork';
else
	$breadcrumbs['websites'] = '/projects/websites';
    
    //$key = ;
    //debug($key);
    $breadcrumbs[ $project['Project']['name'] ] = '/projects/view/' . urlencode($project['Project']['name']);

echo $this->element('breadcrumbs', array('breadcrumbs' => $breadcrumbs)); 

?>
<h1 class = 'vine'><?= $header ?></h1>



<?
//debug($images);

$max = count($images);
echo $this->Flash->show();

if ($mode == "upload"){

echo $this->Form->create('Image', array('action' => 'upload', 'type' => 'file'));

if (isset($projectId))	echo "<input type='hidden' name='data[Project][id]' value='" . $projectId . "' />\n";
?>
<input type="hidden" id="ImageType" name="data[type]" value="<?= $imageType ?>" />
<input type='hidden' name='data[order]' value='<? echo (count($images) + 1) ?>' />


<div id="tabs" style = 'margin-bottom: 40px;'>
	<ul>
		<li><a href="#tabs-1">Upload an image</a></li>
<? //		<li><a href="#tabs-2">Upload an avatar image</a></li>   ?>  
	</ul>
	<div id="tabs-1">
		<br/>
<?

if ($max >= 20){
	echo $this->Flash->show("Each subject is allowed a maximum of 20 images.  No more images can be uploaded for this subject.");	
} else {
?>
		<div class = 'submit' style = 'width: 80px; float: right; margin-top: 40px;'><input type="submit" value="Upload"></div>
        <div class = 'input first' style = 'float: left; clear:none;'>
        	<label for="ImageFile">File</label>
    		<input type="file" id="ImageFile" name="data[Image][file]"  onClick = "$('#ImageUrl').val('');">
        </div>
        <div class = 'input last'>		
        	<label style = 'clear:both;' for="ImageUrl">or URL</label>
        	<input type="text" id="ImageUrl" style="width: 400px;" name="data[Image][url]" onClick = "document.getElementById('ImageFile').setAttribute('type', 'input'); document.getElementById('ImageFile').setAttribute('type', 'file');">
        </div> 
<?
}
?>    
<p>Any image you upload should:</p>
<ul>
	<li>Be at least 300px x 300px, and less than 2000px x 2000px.</li>
    <li>Be less than 2mb in size.</li>
    <li>Clearly picture the subject.</li>
    <li>Be a .jpg, .gif, or .png</li>
    <li>Not involve nudity or other inappropriate content.</li>
</ul>
	</div>
<? /*
	<div id="tabs-2">
		<br/>
        
		<div class = 'submit' style = 'width: 80px; float: right; margin-top: 40px;'><input type="submit" value="Upload"></div>
        <div class = 'input first' style = 'float: left; clear:none;'>
        	<label for="ImageAvatarFile">File</label>
    		<input type="file" id="ImageAvatarFile" name="data[Image][avatarFile]">
        </div>
        <div class = 'input last'>		
        	<label style = 'clear:both;' for="ImageAvatarUrl">or URL</label>
        	<input type="text" id="ImageAvatarUrl" style="width: 400px;" name="data[Image][avatarUrl]">
        </div> 
        
<p>Several of the games on the site use 'avatar' icons to represent you.  If you like, you can upload a custom avatar here.  
The image you use upload for your custom avatar should:</p>
<ul>
	<li>Be 40px x 40px.</li>
    <li>Be a .jpg, .gif, or .png</li>
    <li>Not involve nudity or other inappropriate content.</li>    
</ul>
	</div>    
*/
?>
</div>



<?

	
	


echo $this->Form->end();

}//end if mode = 'upload'



if (!empty($images)){
				
	if ($mode == 'upload')	echo "<h3 class = 'swoosh'>Edit existing images</h3>";
	
	//debug($images);
	
	
	foreach($images as $key => $image){
		
		$counter = $key + 1;
		$source = "";
		$thumbnailSource = "";
		
		$match = false;
		if (isset($imageId)){
			if ($imageId == $image['id'])	$match = true;
		} else {
			$match = true;	
		}
		
		if ($match){//if an image id has been provided, show only that image.  Otherwise, show all.
		
			foreach ($image['ImageVersion'] as $imageVersion){
			
				if ($imageVersion['version'] == 'medium_large'){
					$source = $imageVersion['source'];
				}
				if ($imageVersion['version'] == 'thumbnail'){
					$thumbnailSource = $imageVersion['source'];
				}			
			
			}
            
            //debug($image['ImageVersion']);
			
			if (empty($source)){
				foreach ($image['ImageVersion'] as $imageVersion){	
					if ($imageVersion['version'] == 'medium')	$source = $imageVersion['source'];	
				}	
				//http://ecx.images-amazon.com/images/I/51BR3RNVKZL._SL250_AA250_.jpg
				if (!empty($source))	$source = str_replace("250_AA250", "500_AA500", $source);						
			}
			
/*
//array('action' => 'upload')
echo $this->Form->create('Image');

	echo $this->Form->input('Image.title', array(
					'label' => 'Caption', 
					'style' => 'width: 180px;',
					'div' => 'input first'
				)
	);
	echo $this->Form->input('Image.order', array(
					'label' => 'Order', 
					'style' => 'width: 20px;',
					'div' => 'input last'
				)
	);	

echo $this->Form->end();
<input type='text' id='ImageOrder' style='width: 20px;' name='data[Image][order]'>

*/		
			$order = $counter;

			if (isset($image['ImagesProject']['order']))	$order = $image['ImagesProject']['order'];
			
			$access = false;
			
			//debug($image);
			//debug($user);
			//$access = true;
			if (($image['user_id'] == $user['User']['id']) || ($user['User']['type'] == 'admin')) $access = true;
			
	//debug($order);

			echo "<div id = 'imageContainer" . $image['id'] . "' class = 'imageContainer'>
	<div class = 'imageMenu'>

<form accept-charset='utf-8' class = 'ajaxForm' action='/images/update.json' method='post' onSubmit = \"lastUpdatedImageId = '" . $image['id'] . "';\">
<input type='hidden' name='_method' value='POST' />
<input type='hidden' name='data[Image][id]' value='" . $image['id'] . "' />
<input type='hidden' id = 'oldOrder" . $image['id'] . "' name='data[oldOrder]' value='" . $order . "' />
<input type='hidden' name='data[type]' value='" . $imageType . "' />\n";

if (isset($projectId))	echo "<input type='hidden' name='data[Project][id]' value='" . $projectId . "' />\n";

	if ($access)	echo "<div class = 'input'>
<label for='ImageTitle" . $image['id'] . "'>Caption</label>
<input id = 'ImageTitle" . $image['id'] . "' type='text' maxlength='200' style='width: 160px;' name='data[Image][title]' value = \"" . $image['title'] . "\">
</div>\n";


	echo "<div class = 'input'>
<label for='ImageOrder" . $image['id'] . "' style = 'width:40px; display:inline;'>Order</label>
<select id='ImageOrder" . $image['id'] . "' class = 'imageOrder' name='data[order]' onChange = \"updateOrder('" . $image['id'] . "', $(this).val()); return false;\">";

	for ($i = 1; $i <= $max; $i++){
		$selected = "";
		if ($i == $order) $selected = "SELECTED ";
		echo "	<option value = '" . $i . "' $selected>" . $i . " of " . $max . "</option>\n";
	}

	echo "</select>
</div>\n";

	if (($access)&&(preg_match("|^/img|i", $source)))	echo	"<div class = 'input'>
<img src =  '/img/set_crop.png' style = 'vertical-align:middle;' />&nbsp;&nbsp;<a href = '#' id = 'editCrop" . $image['id'] . "' onClick = \"editCrop('" . $image['id'] . "'); return false;\">Edit crop point</a>
<div id = 'previewContainer" . $image['id'] . "' class = 'previewContainer'>
<div id = 'previewExplanation" . $image['id'] . "' class = 'previewExplanation small'><strong>Current thumbnail.</strong>  To change, move the selection box.</div>
<div id = 'preview" . $image['id'] . "' class = 'preview'><img id = 'previewImage" . $image['id'] . "' src = '" . $thumbnailSource. "' style='width: 80px; height: 80px;' /></div>
<label class = 'small'><input name='data[avatar]' type='checkbox' value='1' />&nbsp;&nbsp;use as avatar</label>
</div>
</div>\n";

if ($access)	echo	"<div class = 'input last'>
<img src =  '/img/delete.png' style = 'vertical-align:middle;' />&nbsp;&nbsp;<a href = '#' onClick = \"deleteImage('" . $image['id'] . "', '" . $imageType . "', $('#oldOrder" . $image['id'] . "').val()); return false;\">Delete this image</a>
</div>\n";

	if ($image['user_id'] != $user['User']['id'])	echo	"<div class = 'input last'>
<img src =  '/img/flag.png' style = 'vertical-align:middle;' />&nbsp;&nbsp;<a href = '#' onClick = \"flagImage('" . $image['id'] . "'); return false;\">Flag this image</a>
</div>\n";


	if ($mode == 'upload')
		$buttonText = "Update this image";
	else
		$buttonText = "Continue &gt";

	echo "<div class = 'submit' style = 'text-align:left; margin-bottom: 10px;'><input type='submit' value='$buttonText'></div>

</form>
	
	</div>		
	<div class = 'image round'>
		<img id = 'image". $image['id'] . "' class = 'areaSelect' src = '" . $source . "' title = \"" . $image['title'] . "\" />
	</div>
</div>\n";

		}//end if $match

	}//end foreach
				
}//end if


?>
    



