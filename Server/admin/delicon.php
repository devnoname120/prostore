<?php
session_start();

require '../functions.php';


// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_GET['hbid']) AND is_array($currhb = get_hbinfo($_GET['hbid'])))
{
	del_icon($_GET['hbid']);
	
	// Redirect to the page of the homebrew
	header('Location: ./view.php?hbid=' . $_GET['hbid'] . '&done=3');
}
?>