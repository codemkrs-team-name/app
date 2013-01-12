<style>
.gist .line_numbers {font-size:12px; line-height:20.9px;}
</style>

<div style = 'float:right; margin-right: 20px;' class='quiet'>Posted on <?= $post['Post']['post_date'] ?></div>

<?php
	echo $this->element('breadcrumbs', array('breadcrumbs' => array(
		'blog' => '/posts'																	
	)));
?>

<h1><?= $post['Post']['post_title'] ?></h1>



<?php
echo $post['Post']['post_content'];

echo $this->element('comments');
?>








