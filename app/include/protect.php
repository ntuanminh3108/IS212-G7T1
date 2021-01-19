<?php
//importing required files.
require_once 'token.php';
require_once 'common.php';

// checks if the user logged in correctly via the homepage.
$userid = '';
if  (isset($_SESSION['userID'])) {
	$userid = $_SESSION['userID'];
}

# send user back to the login page with the appropriate message if it was not
if (!isset($_SESSION['userID'])) {
	$_SESSION['errors'] = [];
	$error = "Please enter username.";
	array_push($_SESSION['errors'], $error);
	header("Location: login.php");
}
?>