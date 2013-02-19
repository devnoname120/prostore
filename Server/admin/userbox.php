<?php

	echo '<div class="userbox">';
	echo '<h3>User no. ' . $curruser['id'] . '</h3>';
	echo '<b>Login:</b> ' . $curruser['login'] . '<br />';
	echo '<b>Rank:</b> ' . $curruser['rank'] . '<br />';
	echo '<br />';
	echo '<strong>Added entries:</strong> ' . $curruser['added_entries'] . '<br />';
	echo '<strong>Modified entries:</strong> ' . $curruser['modified_entries'] . '<br />';
	echo '<form action="connect.php" method="POST"><input type="hidden" name="disconnect" value=1 /><br /><input type="submit" value="Disconnect" /></form>';
	echo '</div>';
?>