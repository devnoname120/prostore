<?php
// Parameters: -hbid: a number between 1 and 99999999
//			   -scid: the number of the screenshot (screenshot 1, 2 ...)

require '../functions.php';


// Test infos are provided and not wrong
if (isset($_GET['hbid']))
{
	$_GET['hbid'] = (int)$_GET['hbid'];
	
	$icon_address = $icon_address = get_icon($_GET['hbid']);
	
// Display errors; < 0 means error
	if ($icon_address < 0)
	{
		// Warns the browser we're using xml
		header('content-type: application/xml');
		
		// Tells about the xml version we are using
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		// Our tag identifying the informations
		echo '<prostore_entry>';
		
		echo '<error>' . $icon_address . '</error>';

		echo '</prostore_entry>';
	}
	else
	{
		header('Location: ' . $icon_address);
	}
}
?>



