<?php

// import required files.
require_once 'common.php';

function dropSection($userid, $course, $section) {
    
    // Initialize variables needed for storing detected errors 
    $_SESSION['errors'] = [];
    $errors = array();

    // Initialize all the DAO objects for access into the database 
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();
    $prerequisiteDAO = new PrerequisiteDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $settingsDAO = new SettingsDAO();
    $bidDAO = new BidDAO();

    # Check if course code referenced within this line exists within the course database 
    if($courseDAO->retrieveByCourse($course) == null) {
        # If an empty array is returned, it means that the course code referenced does not exist in the course table
        $errors[] = "invalid course";
    } else {
        # If the course exists within the database
        # Check if section number referenced within this line exists within the section database 
        $section_check = $sectionDAO->retrieveOnlySectionByCourse($course);
        if(!in_array($section, $section_check)) {
            # If an empty array is returned, it means that the course code referenced does not exist in the course table
            $errors[] = "invalid section";
        } else {
        }
    }

    # Check if the userid already exists within the database 
    if ($studentDAO->retrieveByUserID($userid) == null){
        $errors[] = "invalid userid";
    } 

    # Check if the bidding round is still active 
    if ($settingsDAO->getBiddingAllowed() == 0) {
        $errors[] = "round not active";
    }

    # Generate return values 
    if (count($errors) == 0) {

        $bidObj = $bidDAO->retrieveBidByUserIDAndCourseAndRound($userid,$course,2);
        # Obtain the bid amount
        if ($bidObj === NULL) {
            $bidObj = $bidDAO->retrieveBidByUserIDAndCourseAndRound($userid,$course,1);
        }
        $amount_to_refunded = $bidObj->getAmount();

        # Update student's wallet 
        $studentObj = $studentDAO->retrieveByUserID($userid);
        $new_wallet_amount = $studentObj->addEdollar($amount_to_refunded);
        $studentDAO->updateBalance($userid, $new_wallet_amount);

        # Remove the bid from the database 
        $bidDAO->removeBid($userid, $course);

        #Increase vacancy by 1
        $newVacancy = $sectionDAO->retrieveBySectionAndCourse($course,$section)->getVacancy() + 1;
        $sectionDAO->updateVacancy($course,$section, $newVacancy);

        $result = [ 
            "status" => "success",
        ];
    } else {
        $result = [ 
            "status" => "error",
            "message" => $errors
        ];
    }

    # Return the generated value 
    return $result;

}

?>