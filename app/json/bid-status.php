<?php
require_once '../include/common.php';
require_once '../include/token.php';

if (isset($_REQUEST['r'])) {
    $request = json_decode($_REQUEST['r']);
}
$errors = array();
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
        $sectionDAO = new SectionDAO();
        $section = $sectionDAO->retrieveBySectionAndCourse($request->course, $request->section);
        if ($section == '') {
            $errors[] = ' invalid section';
        }
        else {
            $settings = new SettingsDAO();
            $round = $settings->getRoundNumber();
            $biddingAllowed = $settings->getBiddingAllowed();
            $bidDAO = new BidDAO();
            $studentDAO = new StudentDAO();
            if ($round == 1) {
                $vacancies = $section->getVacancy();
                $bidRecords = array();
                $allBids = $bidDAO->getAllBidsInRoundCourseSection(1,$request->course, $request->section);
                if ($allBids == null) {
                    $minBidPrice = 10;
                    
                }
                else {
                    $minBidPrice = $allBids[0]->getAmount();
                    foreach ($allBids as $bid) {
                        $bidPrice = $bid->getAmount();
                        if ($bidPrice < $minBidPrice) {
                            $minBidPrice = $bidPrice;
                        }
                    }
                    foreach ($allBids as $bid) {
                        $userID = $bid->getUserid();
                        $amount = $bid->getAmount();
                        $balance = $studentDAO->retrieveByUserID($userID)->getEdollar();
                        $bidResult = $bid->getStatus();
                        if ($bidResult == 'P') {
                            $status = 'pending';
                        }
                        elseif ($bidResult == 'S') {
                            $status = "success";
                        }
                        elseif ($bidResult == 'F') {
                            $status = 'fail';
                        }
                        $bidRecords[] = [
                            "userid" => $userID,
                            "amount" => $amount,
                            "balance" => floatval($balance),
                            "status" => $status
                        ];
                    }
                }
            }
            elseif ($round == 2) {
                $vacancies = $section->getSize() - count($bidDAO->retrieveBidBySectionForRound($request->course,$request->section,'S',1));
                $allBids = $bidDAO->getAllBidsInRoundCourseSection(2,$request->course, $request->section);
                if ($biddingAllowed == 0) {
                    $vacancies = $vacancies + count($bidDAO->retrieveBidBySectionForRound($request->course,$request->section,"F",2));
                    $allBids = $bidDAO->retrieveBidBySectionForRound($request->course,$request->section,"S",1);
                    $allBids[] = $bidDAO->retrieveBidBySectionForRound($request->course,$request->section,"S",2);
                }
                $minBidPrice = $section->getMinBid();

                $allBids = $bidDAO->getAllBidsInRoundCourseSection(2,$request->course, $request->section);
                $allBids = array_merge($allBids, $bidDAO->getAllBidsInRoundCourseSection(1,$request->course, $request->section) )
                foreach ($allBids as $bid) {
                    $userID = $bid->getUserid();
                    $amount = $bid->getAmount();
                    $balance = $studentDAO->retrieveByUserID($userID)->getEdollar();
                    $bidResult = $bid->getStatus();
                    if ($bidResult == 'P') {
                        $status = 'pending';
                    }
                    elseif ($bidResult == 'S') {
                        $status = "success";
                    }
                    elseif ($bidResult == 'F') {
                        $status = 'fail';
                    }
                    if ($round == 2 && $biddingAllowed == 0 && $bidResult == 'S') {
                        $bidRecords[] = [
                            "userid" => $userID,
                            "amount" => $amount,
                            "balance" => floatval($balance),
                            "status" => $status
                        ];
                    }
                    elseif ($round == 2 && $biddingAllowed == 1) {
                        $bidRecords[] = [
                            "userid" => $userID,
                            "amount" => $amount,
                            "balance" => floatval($balance),
                            "status" => $status
                        ];
                    }

                }
            }
        }
    }
}

if (empty($errors)) {
    $result = [
        'status' => "success",
        "vacancy" => $vacancies,
        "min-bid-amount" => floatval($minBidPrice),
        "students" => $bidRecords
    ];
}
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
}

header("Content-type:application/json");
echo json_encode($result, JSON_PRESERVE_ZERO_FRACTION | JSON_PRETTY_PRINT);