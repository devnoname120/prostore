<?php
session_start();

require '../functions.php';


function RetError($tmpfile, $oZip, $string)
{
	$oZip->close();
	unlink($tmpfile);
	die($string);
}


// If the user has the rights
if(isset($_SESSION['login']) AND is_array($curruser = GetUserInfo($_SESSION['login'])) AND isset($_POST['hbid']) AND is_array($currhb = get_hbinfo($_POST['hbid'])))
{
	// If the content is OK
	if (!empty($_FILES) AND $_FILES['upload']['error'] <= 0 AND $_FILES['upload']['size'] > 0 AND $_FILES['upload']['size'] < (1024 * 1024 * 1024) AND is_uploaded_file($_FILES['upload']['tmp_name']))
	{
		// Check the file type
		if($_FILES['upload']['type'] == 'application/zip')
		{
			$tmpfile = GetContentUploadPath($_POST['hbid'], 1);
			move_uploaded_file($_FILES['upload']['tmp_name'], $tmpfile);
			
			$tmpzip = new ZipArchive;
			// Open the file and make sure it is valid
			$res = $tmpzip->open($tmpfile, ZIPARCHIVE::CHECKCONS);
			
			if ($res === TRUE)
			{
				if ($currhb['category'] == 'game' OR $currhb['category'] == 'emulator' OR $currhb['category'] == 'utility')
				{
					// Try to find the EBOOT.PBP file without taking care of the case
					if ($tmpzip->locateName('EBOOT.PBP', ZIPARCHIVE::FL_NOCASE) === FALSE)
					{
						RetError($tmpfile, $tmpzip, 'EBOOT.PBP is missing');
					}
				}
				elseif ($currhb['category'] == 'plugin')
				{
					// Do some checks
				}
				
				// We finished our tests
				$tmpzip->close();

				$finalfile = GetContentUploadPath($_POST['hbid'], 0);
				rename($tmpfile, $finalfile);
			}
			else
			{
				RetError($tmpfile, $tmpzip, 'Corrupt or not valid ZIP file');
			}
			
			
		}
	}
	header('Location: ' . './view.php?hbid=' . $_POST['hbid']);
}
?>