<?php

session_start();

require '../functions.php';

// Verify the password
require '../frameworks/libpassword.php';

// No error as far
$error = 0;
// If we want to disconnect
if(isset($_POST['disconnect']) AND $_POST['disconnect'] == 1)
{
	$error = 3;
}
else
{
	if(isset($_POST['login']) AND isset($_POST['password']))
	{
		try
		{
			$hb_bdd = new PDO('mysql:host=localhost;dbname=' . DBNAME, LOGIN, PASSWORD);
		}
		catch (Exeption $e)
		{
			die('Error : ' . $e->getMessage());
		}
		// Locate the account
		$prep_hb_infos = $hb_bdd->prepare('SELECT * FROM hb_admin WHERE login = :login');
		$prep_hb_infos->execute(array('login' => $_POST['login']));
		
		// Avoids injections
		$_POST['password'] = strip_tags($_POST['password']);
		
		if ($data = $prep_hb_infos->fetch())
		{
			// Check the password
			$good_login = password_verify($_POST['password'], $data['password']);
			
			if (!$good_login)
			{
				// Wrong password
				$error = 1;
			}
			else
			{
				$_SESSION['login'] = $_POST['login'];
			}
		}
		else
		{
			// Wrong login
			$error = 1;
		}
	}
}

// Session, login, and password are OK
if ($error == 0)
{
	header('Location: ' . './panel.php');
}
else
{
	// Destroy sesson, go back to the connection and tell the error
	session_destroy();
	header('Location: ' . './index.php?error=' . $error);
}
	
?>