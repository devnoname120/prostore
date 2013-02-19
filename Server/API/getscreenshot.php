<?php
// Parameters: -hbid: a number between 1 and 99999999
//			   -scid: the number of the screenshot (screenshot 1, 2 ...)

require '../functions.php';


// Test infos are provided and not wrong
if (isset($_GET['hbid']) AND isset($_GET['scid']))
{
	$_GET['hbid'] = (int)$_GET['hbid'];
	$_GET['scid'] = (int)$_GET['scid'];
	
	$sc_address = get_screenshot($_GET['hbid'], $_GET['scid']);
	
// Display errors; < 0 means error
	if ($sc_address < 0)
	{
		// Warns the browser we're using xml
		header('content-type: application/xml');
		
		// Tells about the xml version we are using
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		// Our tag identifying the informations
		echo '<prostore_entry>';
		
		echo '<error>' . $sc_address . '</error>';

		echo '</prostore_entry>';
	}
	else
	{
		header('Location: ' . $sc_address);
	}
}
?>

