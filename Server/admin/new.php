<?php

session_start();

require '../functions.php';


// No error as far
$error = 0;



function CheckFields($aFields)
{
	if(isset($aFields['name']) AND isset($aFields['author']) AND isset($aFields['category']) AND isset($aFields['version']) AND isset($aFields['release']) AND isset($aFields['sdescription']) AND isset($aFields['ldescription']))
	{
		return true;
	}
	else
	{
		$error = -1;
		return false;
	}
}

if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND CheckFields($_POST))
{
	try
	{
		$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Error : ' . $e->getMessage());
	}
	// Create the HB
	$hb_entry = $hb_bdd->prepare('INSERT INTO hb_database(name, author, category, version, `release`, sdescription, ldescription) VALUES(:name, :category, :version, :release, :sdescription, :ldescription)');
	$hb_entry->execute(array(
		'name' => $_POST['name'],
		'author' => $_POST['author'],
		'category' => $_POST['category'],
		'version' => $_POST['version'],
		'release' => $_POST['release'],
		'sdescription' => $_POST['sdescription'],
		'ldescription' => $_POST['ldescription']
		)) or $error = -2;
	$hbid = $hb_bdd->lastInsertId();

	// Update the user statistics
	action_added_entry($curruser['id'], $curruser['added_entries']);

	// Update the translation databases
	TranslateEntry($hbid, $_POST['sdescription'], $_POST['ldescription']);
	

	header('Location: ' . './view.php?hbid=' . $hbid . '&done=0');
	
}
echo $error;
?>