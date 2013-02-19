<?php
session_start();

require '../functions.php';


// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_POST['hbid']) AND is_array($currhb = get_hbinfo($_POST['hbid'])))
{
	// If the icon is OK
	if (!empty($_FILES) AND $_FILES['upload']['error'] <= 0 AND $_FILES['upload']['size'] > 0 AND $_FILES['upload']['size'] < 2000000 AND is_uploaded_file($_FILES['upload']['tmp_name']))
	{	
		// Check the file type
		if($_FILES['upload']['type'] == 'image/jpeg' OR $_FILES['upload']['type'] == 'image/png' OR $_FILES['upload']['type'] == 'image/gif' OR $_FILES['upload']['type'] == 'image/bmp')
		{
			$upload_dest = GetIconUploadPath($_POST['hbid']);

			$tmp_img = $_FILES['upload']['tmp_name'];
			// Convert the image or change the compression
			
			if ($_FILES['upload']['type'] == 'image/jpeg')
			{
				$img = imagecreatefromjpeg($tmp_img);
			}
			if ($_FILES['upload']['type'] == 'image/png')
			{
				$img = imagecreatefrompng($tmp_img);
			}
			if ($_FILES['upload']['type'] == 'image/gif')
			{
				$img = imagecreatefromgif($tmp_img);
			}			
			if ($_FILES['upload']['type'] == 'image/bmp')
			{
				// Not official: in functions.php by DHKold
				$img = ImageCreateFromBMP($tmp_img);
			}
			
			// Finally save it with the chosen compression
			imagepng($img, $upload_dest, 9);
			
			// move_uploaded_file is unecessary
			
			header('Location: ' . './view.php?hbid=' . $_POST['hbid'] . '&done=4');
		}
	}
}
?>