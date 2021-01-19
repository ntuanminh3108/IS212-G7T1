<?php
// import required files.
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/RoundClearing.php';

// initialises an array to store all error messages.
$errors = array();

// validations for token.
if (!isset($_REQUEST['token'])) {
    $errors[] = "missing token";
}

elseif (empty($_REQUEST['token'])) {
    $errors[] = "blank token";
}

elseif (!verify_token($_REQUEST['token'])) {
    $errors[] = "invalid token";
}

else {
    // validate if round already ended.
    // if round already ended, generate appropriate error message.
    $settingsDAO = new SettingsDAO();
    $bidding_allowed = $settingsDAO->getBiddingAllowed();
    if ($bidding_allowed == 0) {
        $errors[] = 'round already ended';
    }
    else {
        // if not, end round.
        $settingsDAO->setBiddingAllowed(0);
        $round_number = $settingsDAO->getRoundNumber();
        if ($round_number == 1) {
            RoundOneClearing();    
        }
        else {
            RoundTwoEnd();
        }
    }
}

// if no errors, return success message.
if (empty($errors)) {
    $result = ["status" => "success"];
}

// else, return appropriate error message.
else {
    $result = ["status" => "error",
    "message" => $errors];
}

// encode result in JSON format.
header("Content-type:application/json");
echo json_encode($result, JSON_PRETTY_PRINT);
?>