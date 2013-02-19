<?php
// Parameters: hbid: a number between 1 and 99999999

require '../functions.php';

// No error as far
$error = 0;


// Test infos are provided and not wrong
if (isset($_GET['hbid']))
{
	$_GET['hbid'] = (int)$_GET['hbid'];
	
	
	$content_address = get_content($_GET['hbid']);
	if (is_string($content_address))
	{
		// Redirect to it
		header('Location: ' . $content_address);
		exit;
	}
	else
	{
		$error = $content_address;
	}
}
// Display errors; 0 means no error
if ($error != 0) {
	// Warns the browser we're using xml
	header('content-type: application/xml');
	
	// Tells about the xml version we are using
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	
	// Our tag identifying the informations
	echo '<prostore_entry>';
	
	echo '<error>' . $error . '</error>';
	
	echo '</prostore_entry>';
}
?>

