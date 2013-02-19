<?php
session_start();

require '../functions.php';


// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_GET['hbid']) AND is_array($currhb = get_hbinfo($_GET['hbid'])) AND is_string(get_screenshot($_GET['hbid'], $_GET['scid'])))
{
	del_screenshot($_GET['hbid'], $_GET['scid']);
	
	// Redirect to the page of the homebrew
	header('Location: ./view.php?hbid=' . $_GET['hbid'] . '&done=2');
}
?>