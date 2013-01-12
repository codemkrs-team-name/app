<?php
echo $this->element('breadcrumbs');
?>
<h1>Blog</h1>

<br/>
<style>
.postDetails {list-style:none; margin-left:0px; margin-bottom:5px; color:#6a6f63; height:20px;}
.postDetails li {display:inline; vertical-align:middle;}
.postImage {float:right; margin:10px 0px 10px 10px;}
.postImage img {width:140px; height:140px;}
</style>
<?php

if (isSet($results)){	


	//debug($results);
		echo $this->element('pagination', array('model' => 'Post', 'location' => 'horizontal top', 'class' => 'center')); 

		$class = "even";
		$counter = 0;	
		foreach($results as $result){	
			if ($class == 'even')	
				$class = "odd";
			else
				$class = "even";
											
?>
		
		<div id = 'post<?= $counter ?>' class = 'post shadow clearfix <?= $class ?>'>      	
 			<div class='postImage'><a href="/posts/view/<?= $result['Post']['post_name'] ?>"><img class='shadow' src='<?= $result['Image']['src'] ?>' /></a></div>
            <h3><a href="/posts/view/<?= $result['Post']['post_name'] ?>"><?= $result['Post']['post_title'] ?></a></h3>
            <?php
            if (!empty($result['Post']['post_excerpt']))	echo "<p class='postExcerpt'>" . $result['Post']['post_excerpt'] . "</p>";
            ?>
            <ol class='postDetails'>
                <li class=''><span class='ico-arrow-right'>&nbsp;</span>&nbsp;posted on: <?= $result['Post']['post_date'] ?></li>
            </ol>          
    	</div><!-- end post -->  

<?php	
			
			$counter++;
		}//end foreach	
	
		if ($counter > 4)	echo $this->element('pagination', array('model' => 'Post', 'location' => 'horizontal bottom', 'class' => 'center'));


}//end if results
?>








