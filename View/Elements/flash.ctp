<?php

if (!isset($type))	$type = 'alert';  //possible types:  alert, success, error
if (!isset($class)) $class = "";	

if (!empty($message)){
	
    $icon = "";
    switch ($type)
    {
    	case "alert":
        	$icon = "ico-info";
        	break;
        case "success":
        	$icon = "ico-check-alt";
        	break;
            
        case "error":
        	$icon = "ico-warning";
        	break;
    
    }
?>
<div class = 'flash shadow flash<?php echo ucwords($type) . " " . $class ?>'>
<table cellpadding = '0' cellspacing = '0'>
    <tr style = 'vertical-align:middle;'>
        <td style = 'text-align: center; width: 100px; font-size:2em;'><span class='<?php echo $icon; ?>'>&nbsp;</span></td>
        <td><?php echo $message; ?></td>
    </tr>
</table>
</div>

<?
}//end if message
?>