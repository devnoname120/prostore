<?php
// Parameters: -category // Can either be all/0, game, emulator, utility, development, firmware, wallpaper, theme
//			   -rstart	// The result we should begin with
//			   -rend    // The result we should end with
//			   -sortby  // How we should sort the results (id, name, version, release, rating, votescount, dlcount)
//			   -desc    // Sort the results in reverse (1 to reverse, 0 to do it in the normal way)



// Some useful functions
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
if (isset($_GET['category']) AND isset($_GET['rstart']) AND isset($_GET['rend']) AND isset($_GET['sortby']) AND isset($_GET['desc']))
{
	$_GET['rstart'] = (int)$_GET['rstart'];
	$_GET['rend'] = (int)$_GET['rend'];
	
	global $lang;
	// Make sure user is using an authorized language
	if (isset($_GET['lang']))
	{
		$lang = CheckLang($_GET['lang']);
	}
	else
	{
		$lang = "en";
	}
	
	$hblist = get_hblist($_GET['category'], $_GET['rstart'], $_GET['rend'], $_GET['sortby'], $_GET['desc'], $lang);
	// Get the informations
	foreach($hblist as $currenthb)
	{
		// Produce informations
		echo '<hbentry hbid="' . $currenthb['id'] . '">';
		echo '<hbname>' . $currenthb['name'] . '</hbname>';
		echo '<hbcategory>' . $currenthb['category'] . '</hbcategory>';
		echo '<hbversion>' . $currenthb['version'] . '</hbversion>';
		echo '<hbrelease>' . $currenthb['release'] . '</hbrelease>';
		echo '<hbvotescount>' . $currenthb['votescount'] . '</hbvotescount>';
		echo '<hbdlcount>' . $currenthb['dlcount'] . '</hbdlcount>';
		echo '<hbsdescription>' . $currenthb['sdescription'] . '</hbsdescription>';
		echo '<hbldescription>' . $currenthb['ldescription'] . '</hbldescription>';
		echo '</hbentry>';
		
	}
}
else
{
	// Error -1 = hbid is not a number or an incorrect number
	$error = -1;
}
// Display errors; 0 means no error
echo '<error>' . $error . '</error>';
echo '</prostore_entry>';
?>

