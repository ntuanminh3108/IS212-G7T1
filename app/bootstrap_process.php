<?php
	// import required files.
	require_once 'include/bootstrap.php';

	// call the bootstrap function, if bootstrapping was done through Web UI.
	$result = doBootstrap();

	// return the result(status, number of records loaded, and error messages).
	$_SESSION['num-record-loaded'] = $result['num-record-loaded'];
	if ($result['status'] == 'success') {
		$_SESSION['bootstrap-message'] = 'Bootstrap successful.';
	}
	else {
		$_SESSION['bootstrap-message'] = 'Bootstrap has errors but bidding round will still start. See error messages below';
		$error_messages = array();
		foreach ($result['error'] as $errors) {
			$error_messages[] = $errors;
		}

		$_SESSION['bootstrap-errors'] = $error_messages;
	}
	
	// redirect to admin homepage.
	header ('Location: admin_homepage.php');
	exit;

?>