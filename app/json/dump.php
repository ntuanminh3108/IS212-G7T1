<?php
// import required files.
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

// if there are errors, return the appropriate error messages.
if (!empty($errors)) {
    $result = [
        "status" => "error",
        "message" => array_values($errors)
    ];
}

// if there are no errors, proceed with dump table.
else {
    // dump table for course.
    $courseDAO = new CourseDAO();
    $allCourseObjects = $courseDAO->retrieveAll();
    $courseResults = [];
    foreach ($allCourseObjects as $aCourseObject) {
        $courseCode = $aCourseObject->getCourse();
        $courseSchool = $aCourseObject->getSchool();
        $courseTitle = $aCourseObject->getTitle();
        $courseDescription = $aCourseObject->getDescription();
        $courseExamDate = $aCourseObject->getExamDate();
        $courseExamStart = $aCourseObject->getExamStart();
        $courseExamEnd = $aCourseObject->getExamEnd();
        $aCourse = [
            'course' => $courseCode,
            'school' => $courseSchool,
            'title' => $courseTitle,
            'description' => $courseDescription,
            'exam date' => $courseExamDate,
            'exam start' => str_replace(":","",$courseExamStart),
            'exam end' => str_replace(":","",$courseExamEnd)
        ];
        $courseResults[] = $aCourse;
    }

    // dump table for section.
    $sectionDAO = new SectionDAO();
    $allSectionObjects = $sectionDAO->getAllSections();
    $sectionResults = [];
    foreach ($allSectionObjects as $aSectionObject) {
        $sectionCourse = $aSectionObject->getCourse();
        // the following is the 'section' property of a section object.
        $sectionSection = $aSectionObject->getSection();
        $sectionDay = intval($aSectionObject->getDay());
        $dayArrayStr = ['', 'Monday', 'Tuesday', 'Wednesday', "Thursday", "Friday", "Saturday", "Sunday"];
        $sectionStart = $aSectionObject->getStart();
        $sectionEnd = $aSectionObject->getEnd();
        $sectionInstructor = $aSectionObject->getInstructor();
        $sectionVenue = $aSectionObject->getVenue();
        $sectionSize = $aSectionObject->getSize();
        $aSection = [
            'course' => $sectionCourse,
            'section' => $sectionSection,
            'day' => $dayArrayStr[$sectionDay],
            'start' => str_replace(":","",$sectionStart),
            'end' => str_replace(":","",$sectionEnd),
            'instructor' => $sectionInstructor,
            'venue' => $sectionVenue,
            'size' => intval($sectionSize)
        ];
        $sectionResults[] = $aSection;
    }

    // dump table for students.
    $studentDAO = new StudentDAO();
    $allStudentObjects = $studentDAO->retrieveAll();
    $studentResults = [];
    foreach ($allStudentObjects as $aStudentObject) {
        $studentUserID = $aStudentObject->getUserID();
        $studentPassword = $aStudentObject->getPassword();
        $studentName = $aStudentObject->getName();
        $studentSchool = $aStudentObject->getSchool();
        $studentEdollar = $aStudentObject->getEdollar();
        $aStudent = [
            'userid' => $studentUserID,
            'password' => $studentPassword,
            'name' => $studentName,
            'school' => $studentSchool,
            'edollar' => floatval($studentEdollar)
        ];
        $studentResults[] = $aStudent;
    }

    // dump table for prerequisites.
    $prerequisiteDAO = new PrerequisiteDAO();
    $allPrerequisiteObjects = $prerequisiteDAO->getAll();
    $prerequisiteResults = [];
    foreach ($allPrerequisiteObjects as $aPrerequisiteObject) {
        $course = $aPrerequisiteObject->getCourse();
        $prerequisite = $aPrerequisiteObject->getPrerequisite();
        $aPrerequisite = [
            'course' => $course,
            'prerequisite' => $prerequisite
        ];
        $prerequisiteResults[] = $aPrerequisite;
    }

    // dump table for course completed.
    $courseCompletedDAO = new CourseCompletedDAO();
    $allCourseCompletedObjects = $courseCompletedDAO->getAll();
    $courseCompletedResults = [];
    foreach ($allCourseCompletedObjects as $aCourseCompletedObject) {
        $userid = $aCourseCompletedObject->getUserID();
        $course = $aCourseCompletedObject->getCourse();
        $aCourseCompleted = [
            'userid' => $userid,
            'course' => $course
        ];
        $courseCompletedResults[] = $aCourseCompleted;
    }

    // dump table for bids.
    $bidDAO = new BidDAO();
    $settingsDAO = new SettingsDAO();
    $roundnumber = $settingsDAO->getRoundNumber();
    $allBidObjects = $bidDAO->getAllBidsInRound($roundnumber);
    $bidResults = [];
    foreach($allBidObjects as $aBidObject) {
        $userid = $aBidObject->getUserID();
        $amount = $aBidObject->getAmount();
        $course = $aBidObject->getCourse();
        $section = $aBidObject->getSection();
        $aBid = [
            'userid' => $userid,
            'amount' => floatval($amount),
            'course' => $course,
            'section' => $section
        ];
        $bidResults[] = $aBid;
    }

    // dump table for section-students.
    $allSectionStudentsObjects = $bidDAO->getAllSuccessfulBids();
    $sectionStudentResults = [];
    foreach ($allSectionStudentsObjects as $aSectionStudentObject) {
        $userid = $aSectionStudentObject->getUserID();
        $amount = $aSectionStudentObject->getAmount();
        $course = $aSectionStudentObject->getCourse();
        $section = $aSectionStudentObject->getSection();
        $aSectionStudent = [
            'userid' => $userid,
            'course' => $course,
            'section' => $section,
            'amount' => floatval($amount)
        ];
        $sectionStudentResults[] = $aSectionStudent;
    }

    // Combine the output.
    $result = [
        'status' => 'success',
        'course' => $courseResults,
        'section' => $sectionResults,
        'student' => $studentResults,
        'prerequisite' => $prerequisiteResults,
        'completed-course' => $courseCompletedResults,
        'bid' => $bidResults,
        'section-student' => $sectionStudentResults
    ];
}


// encodes result into JSON format while preserving the float values of the bid amount.
header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);
?>