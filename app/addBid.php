<?php
//import required files.
require_once 'include/common.php';
// require_once 'include/protect.php';
require_once 'include/updateBid.php';

if (isset($_POST['bid_action'])) {
    if($_POST['bid_action'] == "Submit Bid") {

        // initialises array to store error messages.
        $errors = array();

        // validate if form fields are filled up.
        if (empty($_POST['course']) || empty($_POST['section']) || empty($_POST['bid_amount'])) {
            $errors[] = 'Please fill up all fields';
            $_SESSION['errors'] = $errors;
            header ('Location: add_bid_form.php');
            exit;
        }

        // if form fields are all filled up, update bid using the inputs passed through the form.
        $userid = $_SESSION['userID'];
        $course = $_POST['course'];
        $section = $_POST['section'];
        $amount = $_POST['bid_amount'];

        $result = updateBid($userid, $amount, $course, $section);

        // generate the appropriate messages depending on whether the adding of bid is successful or not.
        if ($result['status'] != 'success') {
            # Add status message 
            $_SESSION['message'] = 'Bid added unsuccessfully.';
            
            # Add error messages
            $_SESSION['errors'] = $result['message'];
        } else {
            $_SESSION['message'] = 'Bid added successfully.';
        }
        // redirect to add_bid_form.php
        header ('Location: add_bid_form.php');
        exit;
    }
}
?>