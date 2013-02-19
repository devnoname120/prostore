<?php

session_start();


// Our functions
require '../functions.php';


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

// No error as far
$error = 0;


if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])))
{

		echo '<!doctype HTML>';
		echo '<html>';
		echo '<head>';
		echo '	<title>Pro Store administration panel</title>';
		echo '	<link rel="stylesheet" href="style.css">';
		echo '</head>';
		echo '<body>';
		
		// User infos are displayed, needs $curruser array containing the info
		require 'userbox.php';

			
		echo '<a href="./create.php"><img src="./image/add.png" alt="Add a homebrew" /></a>';
		
		// Default values
		$_POST['sortby'] = (!isset($_POST['sortby'])) ? 'id' : $_POST['sortby'];
		$_POST['desc'] = (!isset($_POST['desc'])) ? 0 : $_POST['desc'];
		
		// Anti-injection stuff is in the function
		$hblist = get_hblist(0, 0, 100, $_POST['sortby'], $_POST['desc']);

		echo '<table class="hblist">';
		echo '<thead><tr>';
	
		// Display our table headers -- We use fake forms to send POST data and thus choose whether we sort with desc or not
		echo '
		<th>Edit</th><th>Name</th><th>Developer</th><th>Category</th><th>Version</th>
		<th>' . LinkToForm('Date of release', curPageURL(), array(array('post_name' => 'sortby', 'post_value' => 'release'), array('post_name' => 'desc', 'post_value' => ($_POST['sortby'] == 'release' AND $_POST['desc'] == 0) ? 1 : 0))) . '</th>
		<th>' . LinkToForm('Rating', curPageURL(), array(array('post_name' => 'sortby', 'post_value' => 'rating'), array('post_name' => 'desc', 'post_value' => ($_POST['sortby'] == 'rating' AND $_POST['desc'] == 0) ? 1 : 0))) . '</th>
		<th>' . LinkToForm('Number of votes', curPageURL(), array(array('post_name' => 'sortby', 'post_value' => 'votescount'), array('post_name' => 'desc', 'post_value' => ($_POST['sortby'] == 'votescount' AND $_POST['desc'] == 0) ? 1 : 0))) . '</th>
		<th>' . LinkToForm('Number of downloads', curPageURL(), array(array('post_name' => 'sortby', 'post_value' => 'dlcount'), array('post_name' => 'desc', 'post_value' => ($_POST['sortby'] == 'dlcount' AND $_POST['desc'] == 0) ? 1 : 0))) . '</th>
		<th>Short description</th><th>Long description</th>';
		
		echo '</tr></thead>';
		
		foreach ($hblist as $currhb)
		{
			echo '<tr>';
			echo '<td><a href="./view.php?hbid=' . $currhb['id'] . '" ><img src="./image/edit.png" alt="Edit entry" /></a></td><td>' . $currhb['name'] . '</td><td>' . $currhb['author'] . '</td><td>' . $currhb['category'] . '</td><td>' . $currhb['version'] . '</td><td>' . $currhb['release'] . '</td><td>' . $currhb['rating'] . '</td><td>' . $currhb['votescount'] . '</td><td>' . $currhb['dlcount'] . '</td><td>' . $currhb['sdescription'] . '</td><td>' . $currhb['ldescription'] . '</td>';
			echo '</tr>';
		}
		echo '</table>';
		
		echo '</body>';
		echo '</html>';
	
	
}
else
{
	$error = 2;
}


if($error)
{
	header('Location: ' . './index.php?error=' . $error);
}


?>