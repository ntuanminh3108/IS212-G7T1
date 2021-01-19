<?php 
    //import required files.
    require_once 'include/common.php';
    require_once 'include/deleteBid.php';

    //obtain the information required for deleting bids through the form and session variables.
    $userid = $_SESSION['userID'];
    $course = $_GET['course_code'];
    $section = $_GET['section_no'];

    // delete the bid for students. Validations are done through the deleteBid() function.
    $result = deleteBid($userid, $course, $section);

    // convert the status message to a more readable format, and add error messages(if any).
    if ($result['status'] == 'success') {
        # Add status message 
        $_SESSION['message'] = 'Bid deleted successfully.';
    } else {
        # Add status message 
        $_SESSION['message'] = 'Bid deleted unsuccessfully.';
        
        # Add error message
        $_SESSION['errors'] = $result['message'];
    }

    // redirect to student homepage.
    header ('Location: student_homepage.php');
    exit;
?>
