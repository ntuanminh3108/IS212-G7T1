<?php
    //import required files.
    require_once 'include/common.php';

    //redirect to login page if user logs out from their respective homepage.
    if (isset($_POST['logout'])) {
        header ("Location: login.php");
        exit;
    }


    // isMissingOrEmpty(...) is in common.php (userid and password are obtained within the isMissingOrEmpty function itself)
    $errors = [ isMissingOrEmpty ('userID'), 
                isMissingOrEmpty ('password') ];
    // Filters all the successful isMissingOrEmpty fields
    $errors = array_filter($errors);

    // If there are anything left within the $errors array, it means that the userid/password failed the isMissingOrEmpty check 
    if (!isEmpty($errors)) {
        $result = [
            "status" => "error",
            "messages" => array_values( $errors)
            ];
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
    }
    else{
        $userID = $_POST['userID'];
        $password = $_POST['password'];
        
        # check if userid and password are right. generate a token and return it in proper json format
        # Admin validation
        $settingsDAO = new SettingsDAO();
        $adminPassword = $settingsDAO->getPassword();
        if ($userID == "admin" && $password == $adminPassword) {
            
            $_SESSION['userID'] = 'admin';
            header("Location: admin_homepage.php");
            exit;
        }

        elseif ($userID == "admin" && $password <> $adminPassword) {
            $errors = "Invalid userID/password";
            $_SESSION['errors'] = [];
            array_push($_SESSION['errors'], $errors);
            header ('Location: login.php');
            exit;
        }

        #Validation for student.
        else {
            $studentDAO = new StudentDAO();
            $student_object = $studentDAO->retrieveByUserID($userID);
            if ($student_object != null) {
                $password_match = $student_object->authenticate($password);
                if ($password_match) {
                    $_SESSION["userID"] = $userID;
                    header ("Location:student_homepage.php");
                    exit;
                }
                else {
                    $errors = "Invalid userID/password";
                    $_SESSION['errors'] = [];
                    array_push($_SESSION['errors'], $errors);
                    header ('Location: login.php');
                    exit;
                }
            }
        }

        //if there are errors with authenticating, redirect to respective homepage.
        $errors = "Invalid userID/password";
        $_SESSION['errors'] = [];
        array_push($_SESSION['errors'], $errors);
        header ('Location: login.php');
        exit;
    }
?>