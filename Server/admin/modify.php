<?php

session_start();

require '../functions.php';


// No error as far
$error = 0;

function CheckFields($aFields)
{
	if(isset($aFields['id']) AND isset($aFields['name']) AND isset($aFields['author']) AND isset($aFields['category']) AND isset($aFields['version']) AND isset($aFields['release']) AND isset($aFields['sdescription']) AND isset($aFields['ldescription']))
	{
		return true;
	}
	else
	{
		$error = -1;
		return false;
	}
}

if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_POST['id']) AND is_array($currhb = get_hbinfo($_POST['id'])) AND CheckFields($_POST))
{

	try
	{
		$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
	}
	catch (Exeption $e)
	{
		die('Error : ' . $e->getMessage());
	}
	// Locate the HB
	$prep_hb_infos = $hb_bdd->prepare('UPDATE hb_database SET name = :name, author = :author, category = :category, version = :version, `release` = :release, sdescription = :sdescription, ldescription = :ldescription WHERE id = :id');
	$prep_hb_infos->execute(array(
		'name' => $_POST['name'],
		'author' => $_POST['author'],
		'category' => $_POST['category'],
		'version' => $_POST['version'],
		'release' => $_POST['release'],
		'sdescription' => $_POST['sdescription'],
		'ldescription' => $_POST['ldescription'],
		'id' => $_POST['id']
		)) or $error = -2;
	if ($error == 0)
	{
		action_modified_entry($curruser['id'], $curruser['modified_entries']);
	}
}

(!$error) ? header('Location: ' . './view.php?hbid=' . $_POST['id'] . '&done=' . $error) : header('Location: javascript:history.go(-1)');

?>