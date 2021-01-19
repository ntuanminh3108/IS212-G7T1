<?php
require_once '../include/common.php';
require_once '../include/token.php';

// decodes the JSON request into PHP format.
if (isset($_REQUEST['r'])) {
    $request = json_decode($_REQUEST['r']);
}

// initialise $error which will store all error messages.
$errors = array();

// Validations for token.
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

    // validations for the request parameters.
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
    $course = $courseDAO->retrieveByCourse($request->course);
    if ($course == '') {
        $errors[] = 'invalid course';
    }
    else {
        $sectionDAO = new SectionDAO();
        $section = $sectionDAO->retrieveBySectionAndCourse($request->course, $request->section);
        if ($section == '') {
            $errors[] = ' invalid section';
        }

        /* if all validations are passed, retrieve the bidding information of a specific section for the current bidding round.
        If no bidding rounds are active, the information for the most recently concluded round is dumped. 
        */
        else {
            $bidDAO = new BidDAO();
            $settingsDAO = new SettingsDAO();
            $roundnumber = $settingsDAO->getRoundNumber();
            $biddingAllowed = $settingsDAO->getBiddingAllowed();
            $allBidObjects = $bidDAO->getAllBidsInRoundCourseSection($roundnumber, $request->course, $request->section);
            $row = 1;
            $dumpResult = [];
            foreach ($allBidObjects as $aBidObject) {
                $userid = $aBidObject->getUserID();
                $amount = $aBidObject->getAmount();
                $bidRound = $aBidObject->getRound();
                // changes to status of a bid object to match the format specified for bid-dump.
                if ($roundnumber == 2 && $bidRound == 2 && $biddingAllowed == 1) {
                    $status = '-';
                }
                elseif ($aBidObject->getStatus() == 'S') {
                    $status = 'in';
                }
                elseif ($aBidObject->getStatus() == 'P') {
                    $status = '-';
                }
                else {
                    $status = 'out';
                }
                $aBid = [
                    'row' => intval($row),
                    'userid' => $userid,
                    'amount' => floatval($amount),
                    'result' => $status
                ];
                $row += 1;
                $dumpResult[] = $aBid;
            }
        }

    }
    }
}
/*  returns the status of the bid-dump (whether it was successful or not).
    returns the appropriate error messages or bid-dump results.
*/
if (empty($errors)) {
    $result = [
        'status' => 'success',
        'bids' => $dumpResult
    ];
}
else {
    $result = [
        'status' => 'error',
        'message' => $errors
    ];
}

// encodes result into JSON format while preserving the float values of the bid amount.
header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);