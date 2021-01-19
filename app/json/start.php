<?php
//import required files.
require_once '../include/common.php';
require_once '../include/token.php';

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
    // If round ended, start round or generate appropriate error message.
    $settingsDAO = new SettingsDAO();
    $round_number = $settingsDAO->getRoundNumber();
    $bidding_allowed = $settingsDAO->getBiddingAllowed();
    if($round_number == 1 && $bidding_allowed == 0) {
            $settingsDAO->setRoundNumber(2);
            $round_number = $settingsDAO->getRoundNumber();
            $settingsDAO->setBiddingAllowed(1);
            
    }
    elseif ($round_number == 2 and $bidding_allowed == 0) {
            $errors[] = 'round 2 ended';
        }
}

// If no errors, return success message and round number.
// Also accounts for cases where round has already started.
if (empty($errors)) {
    $result = [
        "status" => "success",
        "round" => $round_number
    ]; 
}

// If there are errors, return the appropriate error messages.
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ]; 
}

// encodes result in JSON format and preserves the integer data type for round number.
header("Content-type:application/json");
echo json_encode($result, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
?>