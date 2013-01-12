<?
if (!isset($this->request->params['paging'])) return;
if (!isset($model)) return;
if (!isset($class)) $class = "";

$pagination = $this->request->params['paging'][$model];
$page = $pagination['page'];
$pageCount = $pagination['pageCount'];
	
if ($pageCount < 2) {
  return;
}

if (!isset($location)) 
	$location = "paginationHorizontalBottom";
else {
	
	if (stristr($location, "horizontal")){
		if (stristr($location, "top")){
			$location = "paginationHorizontalTop";
		} else {
			$location = "paginationHorizontalBottom";
		}
	} else {//vertical
		if (stristr($location, "right")){
			$location = "paginationVerticalRight";
		} else {
			$location = "paginationVerticalLeft";
		}		
	}
	
}

if (!isset($pagesDisplayed)) $pagesDisplayed = 3;	
$modulus = $pagesDisplayed - 1;
		
$clearFlag = false;
$style = "";
if (stristr($location, "orizontal")){
	$previousImage = "/img/page_left.gif";
	$nextImage = "/img/page_right.gif";
	$clearFlag = true;	
	
} else {
	$previousImage = "/img/page_up.gif";
	$nextImage = "/img/page_down.gif";	
}

if (!isset($options)) {
  $options = array();
}

//PaginatorHelper Options for the numbers, (before, after, model, modulus, separator) 
$options['model'] = $model;
$options['escape'] = false; 
$options['modulus'] = $modulus;
$options['seperator'] = false;
$options['first'] = 1;
$options['last'] = 1;

$numbers = $this->Paginator->numbers($options);
$numbers = str_replace("span", "li", $numbers);
$numbers = str_replace(" | ", "\n		", $numbers);
$numbers = str_replace("seperator=\"\"", "", $numbers);	
$numbers = str_replace("...", "<li>...</li>", $numbers);	

//debug($numbers);

echo "	<div class = 'paginationContainer' style='text-align:center;'><ol class = '$class pagination $location'>\n";		

if ($page > 1)	echo "		<li class = 'previous'>" .  $this->Paginator->prev("<img alt = 'Previous Page' src = '$previousImage' />", $options) . "</li>\n";
echo	"		$numbers\n";
if ($this->Paginator->hasNext($model) && ($page < $pageCount))	echo "		<li class = 'next'>" . $this->Paginator->next("<img alt = 'Next Page' src = '$nextImage' />", $options) . "</li>\n";
echo "	</ol></div>\n\n";
if ($clearFlag)	echo "\n<div class = 'clear'></div>\n";
?>
  