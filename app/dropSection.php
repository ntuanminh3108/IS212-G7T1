<?php 
    // import required files.
    require_once 'include/common.php';
    require_once 'include/dropSection.php';

    if(isset($_POST['section_to_drop'])) {
        # Get the necessary parameters to call the dropSection function
        $userid = $_SESSION['userID'];
        list($course, $section) = explode("-", $_POST['section_to_drop']);

        $result = dropSection($userid, $course, $section);
    
        if ($result['status'] == 'success') {
            # Add status message 
            $_SESSION['message'] = 'Section dropped successfully.';
        } else {
            # Add status message 
            $_SESSION['message'] = 'Section dropped unsuccessfully.';
            
            # Add error messages
            $_SESSION['errors'] = $result['message'];
        }
    
        // redirect back to drop_section_form.php
        header ('Location: drop_section_form.php');
        exit;
    }
?>