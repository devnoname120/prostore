<?php

session_start();

require '../functions.php';


// No error as far
$error = 0;

// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])))
{
	echo '<!doctype HTML>';
	echo '<html>';
	echo '<head>';
	echo '	<title>Pro Store administration create</title>';
	echo '	<link rel="stylesheet" href="style.css" />';
	echo '</head>';
	echo '<body>';
	echo '<a href="./panel.php"><img src="./image/back.png" alt="Go back" /></a><br />';
	
	// User infos are displayed, needs $curruser array containing the info
	require 'userbox.php';

	echo '<div class="box">';
	echo '<h2>Add a Homebrew</h2>';
	echo '<form method="post" action="new.php">';
	echo '<table class="infotable">';
	echo '<tr><td>Name :</td><td><input type="text" name="name" /></td></tr>';
	echo '<tr><td>Developer:</td><td><input type="text" name="author" /></td></tr>';
	echo '<tr><td>Category :</td><td><select name="category">' . GetOptions(array('game', 'emulator', 'utility', 'plugin', 'development', 'firmware', 'wallpaper', 'theme'), '') . '</select></td></tr>';
	echo '<tr><td>Version : </td><td><input type="text" name="version" value=1.00></td></tr>';
	echo '<input type="hidden" name="sdescription" value="" />';
	echo '<input type="hidden" name="ldescription" value="" />';
	echo '<tr><td><input type="submit" value="Create" /></td></tr>';
	echo '</table>';
	echo '</form>'; 
	echo '</div>';
	echo '</body>';
	echo '</html>';	
	
}





?>