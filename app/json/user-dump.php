<?php
// import required files.
require_once '../include/common.php';
require_once '../include/token.php';

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
    
    if (!isset($request->userid)) { // not $request['userid'] because $request is a PHP Object.
        $errors[] = "missing userid";
    }

    elseif (empty($request->userid)) {
        $errors[] = "blank userid";
    }

    else {
        $studentDAO = new StudentDAO();
        $student = $studentDAO->retrieveByUserID($request->userid);
        if ($student == null) {
            $errors[] = "invalid userid";
        }
        else {
            // if validations pass, obtain all info required for user-dump.
            $userid = $student->getUserID();
            $password = $student->getPassword();
            $name = $student->getName();
            $school = $student->getSchool();
            $edollar = floatval($student->getEdollar());
        }
    }
}

// if no errors, return user-dump based on obtained info.
if (empty($errors)) {
    $result = [
        "status" => "success",
        "userid" => $userid,
        "password" => $password,
        "name" => $name,
        "school" => $school,
        "edollar" => $edollar
    ];
}

// else, return appropriate error message.
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
}

// // encodes result into JSON format while preserving the float values of the e-dollar balance.
header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);
?>