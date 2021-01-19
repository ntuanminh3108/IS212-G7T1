<?php
	// import required files.
	require_once 'token.php';
	require_once 'common.php';

	// checks if the token is passed through.
	$token = '';
	if  (isset($_REQUEST['token'])) {
		$token = $_REQUEST['token'];
	}


	# check if token is not valid
	# reply with appropriate JSON error message
	 if(!verify_token($token)) {
	 	$errors = "You are not authorised to view this page.";
        $_SESSION['errors'] = [];
        array_push($_SESSION['errors'], $errors);
        header ('Location: login.php');
        exit;
	}
?>