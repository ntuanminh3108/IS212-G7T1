<?php
// import required files.
require_once '../include/common.php';
require_once '../include/token.php';

// isMissingOrEmpty(...) is in common.php
$errors = [ isMissingOrEmpty('username'), 
            isMissingOrEmpty('password') ];
$errors = array_filter($errors);


if (!isEmpty($errors)) {
    sort($errors);
    $result = [
        "status" => "error",
        "message" => array_values($errors)
        ];
}
else{
    $username = $_POST['username'];
    $password = $_POST['password'];
    # complete authenticate API

    # check if username and password are right. generate a token and return it in proper json format
    $password_match = FALSE;
    if ($username == 'admin' && $password == 'nFHPBQz6VmLwR') {
        $password_match = TRUE;
    }

    # after you are sure that the $username and $password are correct, you can do
    # generate a secret token for the user based on their username 
    if ($password_match == TRUE) {
        $token = generate_token($username);
        # return the token to the user via JSON
        $result = [
            "status" => "success",
            "token" => $token
        ];
       
    }
	# return error message if something went wrong
    else {
        if ($username <> 'admin') {
            $errors[] = 'invalid username';
        }
        if ($password <> 'nFHPBQz6VmLwR') {
        $errors[] = 'invalid password';
        }
        sort($errors);
        $result = [
            "status" => "error",
            "message" => array_values($errors)
        ];
    }
}

header("Content-type:application/json");
echo json_encode($result, JSON_PRETTY_PRINT);
 
?>