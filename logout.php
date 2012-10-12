<?php
include('./includes/loader.php');
session_destroy();
session_unset();
header("Location: index.php");
die();
?>
