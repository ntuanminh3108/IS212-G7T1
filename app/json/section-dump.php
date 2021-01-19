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

        // validations for request parameters.
        if (!isset($request->course)) {
        $errors[] = "missing course";
        }

        elseif (empty($request->course)) {
        $errors[] = "blank course";
        }

        if (!isset($request->section)) {
        $errors[] = "missing section";
        }

        elseif (empty($request->section)) {
        $errors[] = "blank section";
        }

        else {
        $courseDAO = new CourseDAO();
        $course = $courseDAO->getCourse($request->course);
        if ($course == '') {
            $errors[] = 'invalid course';
        }
        else {
            $sectionDAO = new SectionDAO();
            $section = $sectionDAO->retrieveBySectionAndCourse($request->course, $request->section);
            if ($section == '') {
                $errors[] = ' invalid section';
            }
            else {
                // if all validations pass, proceed with dumping table.
                $bidDAO = new BidDAO();
                $settingsDAO = new SettingsDAO();
                $round = $settingsDAO->getRoundNumber();
                $biddingAllowed = $settingsDAO->getBiddingAllowed();
                if ($round == 2 && $biddingAllowed == 1) {
                    $bids = $bidDAO->retrieveBidBySectionForRound($request->course,$request->section,"S",1);
                }
                else {
                    $bids = $bidDAO->getBidBySection($request->course,$request->section,'S');
                }
                $sectionStudents = [];
                foreach ($bids as $aBid) {
                    $bidRecord = [
                        'userid' => $aBid->getUserID(),
                        'amount' => floatval($aBid->getAmount())
                    ];
                    $sectionStudents[] = $bidRecord;
                }
            }
        }
    }
}
// if there are no errors with the request, return section dump.
if (empty($errors)) {
    $result = [
        'status' => 'success',
        'students' => $sectionStudents
    ];
}

// if there are errors with the request, return the appropriate error messages.
else {
    $result = [
        'status' => 'error',
        'message' => $errors
    ];
}

// encodes result into JSON format while preserving the float values of the bid amount.
header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);

?>