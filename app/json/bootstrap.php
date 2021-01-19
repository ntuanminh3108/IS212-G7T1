<?php
# import required files.
require_once '../include/bootstrap.php';
require_once '../include/protect_json.php';
require_once '../include/common.php';


if (isset($_SESSION['errors'])) {
    $result = [ 
        "status" => "error",
        "error" => $_SESSION['errors']
    ];
    unset($_SESSION['errors']);
}
else {
    /*  call the bootstrap function, which will return the status and number of records loaded.
        if there are any errors in bootstrap, it will also return the filename, line and specific error message on why that line was not added into the database.
    */
    $result = doBootstrap();
}

// encodes the result of the bootstrap into JSON format.
header("Content-type:application/json");
echo json_encode($result, JSON_PRETTY_PRINT);
?>