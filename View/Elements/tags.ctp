<?
if (! empty($tags))
{
?>
<ol class = 'tags'>
<?
if (!empty($label))	echo "<li><strong>" . $label . ":</strong></li>";
foreach ($tags as $tag)
{
	if (!empty($tag['Tag']))	$tag = $tag['Tag'];
?>
	<li><button class='tag tagCount<?= $tag['count'] ?>' href='/projects/index/tagged:<?= urlencode($tag['name']) ?>'><?= $tag['name'] ?></button></li>
<? 
}//end foreach 
?>
</ol><!-- end tags -->
<?
}//end if tags
?>