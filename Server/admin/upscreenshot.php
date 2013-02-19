<?php
session_start();

require '../functions.php';


function reArrayFiles(&$file_post) {

    $file_ary = array();
    $file_count = count($file_post['name']);
    $file_keys = array_keys($file_post);

    for ($i=0; $i<$file_count; $i++) {
        foreach ($file_keys as $key) {
            $file_ary[$i][$key] = $file_post[$key][$i];
        }
    }

    return $file_ary;
}

// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_POST['hbid']) AND is_array($currhb = get_hbinfo($_POST['hbid'])) AND !empty($_FILES))
{
	// Rearrange the files
	$myFILES = reArrayFiles($_FILES['upload']);
	foreach ($myFILES as $currsc)
	{
		// If the screenshot is OK
		if ($currsc['error'] <= 0 AND $currsc['size'] > 0 AND $currsc['size'] < 2000000 AND is_uploaded_file($currsc['tmp_name']))
		{	
			// Check the file type
			if($currsc['type'] == 'image/jpeg' OR $currsc['type'] == 'image/png' OR $currsc['type'] == 'image/gif' OR $currsc['type'] == 'image/bmp')
			{
				$upload_dest = GetScreenshotUploadPath($_POST['hbid']);
				
				$tmp_img = $currsc['tmp_name'];
				// Convert the image or change the compression
				
				if ($currsc['type'] == 'image/jpeg')
				{
					$img = imagecreatefromjpeg($tmp_img);
				}
				elseif ($currsc['type'] == 'image/png')
				{
					$img = imagecreatefrompng($tmp_img);
				}
				elseif ($currsc['type'] == 'image/gif')
				{
					$img = imagecreatefromgif($tmp_img);
				}			
				elseif ($currsc['type'] == 'image/bmp')
				{
					// Not official: in functions.php by DHKold
					$img = ImageCreateFromBMP($tmp_img);
				}
				
				// Finally save it with the chosen compression
				imagepng($img, $upload_dest, 9);
				
				// move_uploaded_file is unecessary here
			}
		}
	}
	
	header('Location: ' . './view.php?hbid=' . $_POST['hbid']);
}
?>