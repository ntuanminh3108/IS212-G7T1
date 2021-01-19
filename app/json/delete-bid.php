<?php
// import required files.
require_once '../include/common.php';
require_once '../include/token.php';
require_once '../include/deleteBid.php';

// decodes JSON request into PHP format.
if (isset($_REQUEST['r'])) {
    $request = json_decode($_REQUEST['r']);
}

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

if (empty($errors) && isset($_REQUEST['r'])) {
    // validations for request parameters.
    if (!isset($request->course)) { // not $request['userid'] because $request is a PHP Object.
        $errors[] = "missing course";
    }

    elseif (empty($request->course)) {
        $errors[] = "blank course";
    }

    if (!isset($request->section)) { // not $request['userid'] because $request is a PHP Object.
        $errors[] = "missing section";
    }

    elseif (empty($request->section)) {
        $errors[] = "blank section";
    }

    if (!isset($request->userid)) { // not $request['userid'] because $request is a PHP Object.
        $errors[] = "missing userid";
    }

    elseif (empty($request->userid)) {
        $errors[] = "blank userid";
    }

}

// if there are errors with the request, return the appropriate error messages.
if (!empty($errors)) {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
}

/*  if no errors with the request, delete the bid.
    Returns the status of the delete-bid, and appropriate error messages (if any).
*/
else {
    $course = $request->course;
    $userid = $request->userid;
    $section = $request->section;
    $result = deleteBid($userid, $course, $section);
}

// encodes result into JSON format while preserving the float values of the bid amount.
header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);
?>