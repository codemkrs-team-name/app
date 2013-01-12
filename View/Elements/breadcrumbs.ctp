<?
$after = "&nbsp;<span class='ico-arrow-right'>&nbsp;</span>&nbsp;";
if (empty($breadcrumbs))	$after = "";
?>
<ol id = 'breadcrumbs'>
	<li><div style='font-size:1.1em; padding-top:2px; float:left;'><a class='ico' href='/'>&#xe012;</a></div><?= $after ?></li>
<? 
if (!empty($breadcrumbs))
{
	$counter = 1;
	$count = count($breadcrumbs);
	foreach($breadcrumbs as $anchor => $link){ 
		if ($count == $counter)	$after = "";
		echo "<li><a href='" . $link . "'>" . $anchor . "</a>" . $after . "</li>\n";
	    $counter++;
	}
} 
?>
</ol><!-- end div breadcrumbs -->
