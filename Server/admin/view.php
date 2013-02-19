<?php
session_start();

require '../functions.php';

// Avoid showing outdated images and information
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

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

// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_GET['hbid']) AND is_array($currhb = get_hbinfo($_GET['hbid'], $lang)))
{
	echo '<!doctype HTML>';
	echo '<html>';
	echo '<head>';
	echo '	<title>Pro Store administration modify</title>';
	echo '<link rel="stylesheet" href="style.css" />';
	echo '</head>';
	echo '<body>';
	echo '<a href="./panel.php"><img src="./image/back.png" alt="Go back" /></a><br />';

	// User info is displayed, needs $curruser array containing the info
	require 'userbox.php';

	if (isset($_GET['done']))
	{
		// Show the success message
		switch ($_GET['done']) {
		case 0:
			echo '<div class=success>This entry was successfully modified</div>';
			break;
		case 1:
			echo '<div class=success>This entry was successfully created</div>';
			break;
		case 2:
			echo '<div class=success>The screenshot was successfully deleted</div>';
			break;
		case 3:
			echo '<div class=success>The icon was successfully deleted</div>';
			break;
		case 4:
			echo '<div class=success>The icon was successfully modified</div>';
			break;
		}
	}


	echo '<h1>' . $currhb['name'] . '</h1>';
	
	echo '<div class="box">';
	echo '<h2>Icon</h2>';
	// Tell the contributor whether an icon is available or not and show it
	echo '<table class="infotable">';
	echo '<tr><td>Icon: </td><td>'; if (is_string($icon_address = get_icon($currhb['id']))) { echo '<img src="' . $icon_address . '" alt="Icon available" /></td><td><a href="./delicon.php?hbid=' . $_GET['hbid'] . '"><img src="./image/delete.png" alt="Delete Icon"/></a></td>'; } else { echo '<span class="error">No icon</span></td>';}
	echo '</tr>';
	echo '<tr><td>Upload a new icon: </td><td><form action="upicon.php" method="post" enctype="multipart/form-data"><input type="hidden" name="hbid" value="' . $currhb['id'] . '" /><input type="hidden" name="MAX_FILE_SIZE" value="2000000" /><input type="file" name="upload" /><input type="submit" value="Go" /></form></td></tr>';
	echo '</table>';
	echo '</div>';
	echo '<div class="box">';
	echo '<h2>General information</h2>';
	echo '<table class="infotable">';
	echo '<tr><td>Id: </td><td>' . $currhb['id'] . '</td></tr>';
	echo '<form method="post" action="modify.php">';
	echo '<input type="hidden" name="id" value="' . $currhb['id'] . '"/>';
	echo '<tr><td>Name :</td><td><input type="text" name="name" value="' . $currhb['name'] . '" /></td></tr>';
	echo '<tr><td>Author :</td><td><input type="text" name="author" value="' . $currhb['author'] . '" /></td></tr>';
	echo '<tr><td>Category :</td><td><select name="category">' . GetOptions($aCategory, $currhb['category']) . '</select></td></tr>';
	echo '<tr><td>Version : </td><td><input type="text" name="version" value=' . $currhb['version'] . ' /></td></tr>';
	echo '<tr><td>Date of release: </td><td><input type="date" name="release" value="' . $currhb['release'] . '"></td></tr>';
	echo '<tr><td>Short Description: </td><td><textarea name="sdescription" rows="2" cols="45">' . $currhb['sdescription'] . '</textarea></td></tr>';
	echo '<tr><td>Long Description: </td><td><textarea name="ldescription" rows="8" cols="45">' . $currhb['ldescription'] . '</textarea></td></tr>';
	echo '<tr><td><input type="submit" value="Save" /></td></tr>';
	echo '</form>';
	echo '</table>';
	echo '</div>';
	echo '<div class="box">';
	echo '<h2>Download information</h2>';
	echo '<table class="infotable">';
	echo '<tr>';
	if (is_string($content_address = get_content($currhb['id']))) {echo '<td>Download link: </td><td><a href="' . $content_address . '"><img src="./image/download.png" alt="'  . $currhb['name'] . '" /></a></td>';} else {echo '<td><span class="error">No available download</span></td>';}
	echo '</tr>';
	// Allows to add/update the homebrew
	echo '<tr><td>Upload the homebrew:</td><td style="text-align: left;"><form action="upcontent.php" method="post" enctype="multipart/form-data"><input type="hidden" name="hbid" value="' . $currhb['id'] . '" /><input type="hidden" name="MAX_FILE_SIZE" value="1073741824" /><input type="file" name="upload" id="upload" /><input type="submit" value="Go" /></form></td></tr>';
	echo '</table>';
	echo '</div>';
	
	// Screenshots
	echo '<div class="box">';
	echo '<h2>Screenshots</h2>';
	echo '<table class="infotable">';
	$scid = 1;
	// Display screenshot while there is one
	while (is_string($currsc = get_screenshot($_GET['hbid'], $scid)))
	{
		echo '<tr><td><img src="' . $currsc . '" alt="Screenshot ' . $scid .'" /></td><td><a href="./delscreenshot.php?hbid=' . $_GET['hbid'] . '&amp;scid=' . $scid . '"><img src="./image/delete.png" alt="screenshot ' . $scid . '"/></a></td></tr>';
		$scid++;
	}
	
	if ($scid == 1) { echo '<tr><td><span class="error">No Screenshot</span></td></tr>'; }
	
	// Allows to upload a screenshot
	echo '<tr><td>Upload a new screenshot: </td><td><form action="upscreenshot.php" method="post" enctype="multipart/form-data"><input type="hidden" name="hbid" value="' . $currhb['id'] . '" /><input type="hidden" name="MAX_FILE_SIZE" value="2000000" /><input type="file" name="upload[]" multiple="multiple"/><input type="submit" value="Go" /></form></td></tr>';
	echo '</table>';
	echo '</div>';
	
	echo '</body>';
	echo '</html>';	
	
}
?>