<?php
session_start();

if(isset($_SESSION['login']))
{
	header('Location: ' . './panel.php');
}
?>

<!DOCTYPE HTML>
<html>
<head>
        <meta charset="utf-8" />
        <title>Prostore admin connection</title>
		<link rel="stylesheet" href="style.css" />
</head>
<body>
	<h1>Prostore administrator connection</h1>
<?php 

if(isset($_GET['error']))
{
	if ($_GET['error'] == 1)
	{
		echo '<div class="error">Wrong username or password</div><br />';
	}
	if ($_GET['error'] == 2)
	{
		echo '<div class="error">Wrong session, please reconnect</div><br />';
	}
	if ($_GET['error'] == 3)
	{
		echo '<div class="error">You are successfully disconnected</div><br />';
	}
}
?>
	<div class="box">
		<form action="connect.php" method="post">
		Login: <input type="text" name="login" /><br />
		Password: <input type="password" name="password" /><input type="submit" value="Confirm" /></form>
	</div>
</body>
</html>