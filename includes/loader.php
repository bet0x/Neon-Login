<?php
require_once('./includes/db.php');
require_once('./includes/functions.php');
require_once('./includes/global_settings.php');

if(isset($_SESSION['user_id'])){
	$sUser = new User($_SESSION['user_id']);
	$LoggedIn = true;
} else {
	$LoggedIn = false;
}

$sErrorMessage = NULL;

?>
