<?php

// Our functions
require '../functions.php';

// No error as far
$error = 0;

// Warns the browser we're using xml
header('content-type: application/xml');
// Tells about the xml version we are using
echo '<?xml version="1.0" encoding="UTF-8"?>';

// Our tag identifying the informations
echo '<prostore_entry>';

// Test infos are provided and not wrong
if (isset($_GET['hbid']))
{
	$_GET['hbid'] = (int)$_GET['hbid'];
	
	$hbinfo = get_hbinfo($_GET['hbid']);
	
	if (is_array($hbinfo))
	{
		// Produce informations
		echo '<hbentry hbid="' . $hbinfo['id'] . '">';
		echo '<hbname>' . $hbinfo['name'] . '</hbname>';
		echo '<hbcategory>' . $hbinfo['category'] . '</hbcategory>';
		echo '<hbversion>' . $hbinfo['version'] . '</hbversion>';
		echo '<hbrelease>' . $hbinfo['release'] . '</hbrelease>';
		echo '<hbvotescount>' . $hbinfo['votescount'] . '</hbvotescount>';
		echo '<hbdlcount>' . $hbinfo['dlcount'] . '</hbdlcount>';
		echo '<hbsdescription>' . $hbinfo['sdescription'] . '</hbsdescription>';
		echo '<hbldescription>' . $hbinfo['ldescription'] . '</hbldescription>';
		echo '</hbentry>';
	}
	else
	{
		// The function returned an error
		$error = $hbinfo;
	}
}
else
{
	// Wrong hb id
	$error = -1;
}

// Display errors; 0 means no error
echo '<error>' . $error . '</error>';
echo '</prostore_entry>';

?>

