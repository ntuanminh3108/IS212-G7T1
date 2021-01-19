<?php

// Function for Round 1 Clearing Processing.
function RoundOneClearing() {
    # Imports required files for processing DAOs and calling their methods.
    require_once 'common.php';
    
    # Initialises BidDAO and SectionDAO.
    $bidDAO = new BidDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();
    $settingsDAO = new SettingsDAO();
    $round = $settingsDAO->getRoundNumber();

    # Gets the sections which have unprocessed bids.
    $biddedSections = array();
    $bidDump = $bidDAO->retrieveBiddedSectionsByStatusAndRound('P', $round);
    $courseSectionCheck = array();
    foreach ($bidDump as $aBid) {
        $courseSectionBidded = [$aBid[2],$aBid[3]];
        if (!in_array($courseSectionBidded,$courseSectionCheck)) {
            array_push($courseSectionCheck,$courseSectionBidded);
            array_push($biddedSections,$aBid);
        }
    }

    # For each sections which have unprocessed bids:
    # 1. Get the course and the section.
    # 2. Get the bids for that particular section as well as the vacancies.
    foreach ($biddedSections as $aBiddedSection) {
        $courseBidded = $aBiddedSection[2];
        $sectionBidded = $aBiddedSection[3];
        $bidsInSection = $bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'P',$round);
        $section = $sectionDAO->retrieveBySectionAndCourse($courseBidded,$sectionBidded);
        $vacancies = $section->getVacancy();

        # If number of bids are less than the vacancies, all are successful.
        # Reduce number of vacancies by amount of bids and update bid status to successful.
        if (count($bidsInSection) < $vacancies) {
            foreach ($bidsInSection as $aBid) {
                $bidSection = $aBid->getSection();
                $bidCourse = $aBid->getCourse();
                $biduser = $aBid->getUserid();
                $bidDAO->updateBidStatus($biduser,$bidCourse,$bidSection,'S',$round);
                $newVacancy = $sectionDAO->retrieveBySectionAndCourse($bidCourse,$bidSection)->getVacancyDrop();
                $sectionDAO->updateVacancy($bidCourse,$bidSection,$newVacancy);
            }
        }
        # Otherwise, sort all bids by bid amount in descending order.
        # Calculate minimum clearing price.
        # All bids above minimum clearing price is successful.
        # For bids at the minimum clearing price, check if there are more than 1 bids made with that price.
        # If there is only one bid made, it succeeds. If not, all bids at that price are unsuccessful.
        # Bids below clearing price are unsuccessful.
        # Reduce vancancies accordingly and refund all unsuccessful bids.
        else {
            $bids = [];
            foreach ($bidsInSection as $aBid) {
                $bids[] = $aBid->getAmount(); //any object field
            }
            array_multisort($bids, SORT_DESC, $bidsInSection);
            $minBidObject = $bidsInSection[$vacancies - 1];
            $minClearingPrice = $minBidObject->getAmount();

            foreach ($bidsInSection as $aBid) {
                $bidAmount = $aBid->getAmount();
                $bidSection = $aBid->getSection();
                $bidCourse = $aBid->getCourse();
                $bidUser = $aBid->getUserid();
                if ($bidAmount > $minClearingPrice) {
                    $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'S',$round);
                    $newVacancy = $sectionDAO->retrieveBySectionAndCourse($bidCourse,$bidSection)->getVacancyDrop();
                    $sectionDAO->updateVacancy($bidCourse,$bidSection,$newVacancy);
                }
                elseif ($bidAmount == $minClearingPrice) {
                    $sameBids = $bidDAO->getBidsWithSameAmount($bidCourse,$bidSection, 'P', $bidAmount);
                    if (count($sameBids) == 1 && (empty($bidDAO->getBidsWithSameAmount($bidCourse,$bidSection, 'F', $bidAmount))) && (empty($bidDAO->getBidsWithSameAmount($bidCourse,$bidSection, 'S', $bidAmount)))) {
                        $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'S',$round);
                        $newVacancy = $sectionDAO->retrieveBySectionAndCourse($bidCourse, $bidSection)->getVacancyDrop();
                        $sectionDAO->updateVacancy($bidCourse,$bidSection,$newVacancy);  
                    }
                    else {
                        $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'F',$round);
                        $newBalance = $bidAmount + $studentDAO->retrieveByUserID($bidUser)->getEdollar();
                        $studentDAO->updateBalance($bidUser, $newBalance);
                    }
                }
                else {
                    $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'F',$round);
                    $newBalance = $bidAmount + $studentDAO->retrieveByUserID($bidUser)->getEdollar();
                    $studentDAO->updateBalance($bidUser, $newBalance); 
                }
            }
        }

    }
}

// Function for Round 2 Dynamic Status/Min Bid.
function RoundTwoClearing($courseBidded, $sectionBidded) {
    $settingsDAO = new SettingsDAO();
    $bidDAO = new BidDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();
    $round = $settingsDAO->getRoundNumber();

    # Getting all the successful bids for the section
    $bidsInSection = $bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',2);
    $section = $sectionDAO->retrieveBySectionAndCourse($courseBidded,$sectionBidded);
    # Finding for the vacancies (Total size - # of successful in R1)
    $vacancies = $section->getSize() - count($bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',1));

    # For every bid, get the bid amount
    $bids = [];
    foreach ($bidsInSection as $aBid) {
        $bids[] = $aBid->getAmount();
    }

    # Sort the array according to the descending order of the bids array (index to index matching) 
    array_multisort($bids, SORT_DESC, $bidsInSection);

    if (count($bidsInSection) < $vacancies) {
        $minBidValue = $section->getMinBid();
    }
    
    elseif(count($bidsInSection) == $vacancies){
        $minBidValue = ($bidsInSection[$vacancies-1])->getAmount() + 1;
    }

    elseif(count($bidsInSection) > $vacancies) {

        $minClearingPrice = ($bidsInSection[$vacancies-1])->getAmount();
        $minBidValue = ($bidsInSection[$vacancies-1])->getAmount() + 1;

        foreach ($bidsInSection as $aBid) {
            $bidAmount = $aBid->getAmount();
            if($bidAmount < $minClearingPrice) {
                $bidSection = $aBid->getSection();
                $bidCourse = $aBid->getCourse();
                $bidUser = $aBid->getUserid();
                $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'F',$round);
            }
        }
    }

    $newVacancy = $section->getSize() - count($bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',1))- count($bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',2));
    $sectionDAO->updateVacancy($courseBidded,$sectionBidded,$newVacancy);
    return $minBidValue;
}

// Function to ensure the appropriate user gets back his/her previously failed bid if another user deletes is successful bid.
function RoundTwoClearingDelete($courseBidded, $sectionBidded) {
    $bidDAO = new BidDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();
    $round = 2;

    $bidsInSection_S = $bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',2);
    $bidsInSection_F = $bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'F',2);
    $bidsInSection = array_merge($bidsInSection_S, $bidsInSection_F);
    $section = $sectionDAO->retrieveBySectionAndCourse($courseBidded,$sectionBidded);
    $vacancies = $section->getSize() - count($bidDAO->retrieveBidBySectionForRound($courseBidded,$sectionBidded,'S',1));

    $bids = [];
    foreach ($bidsInSection as $aBid) {
        $bids[] = $aBid->getAmount();
    }

    array_multisort($bids, SORT_DESC, $bidsInSection);

    foreach(array_slice($bidsInSection, 0, $vacancies) as $aBid) {
        $bidSection = $aBid->getSection();
        $bidCourse = $aBid->getCourse();
        $bidUser = $aBid->getUserid();
        $bidDAO->updateBidStatus($bidUser,$bidCourse,$bidSection,'S',$round);
    }

}

// function to refund all unsuccessful bids made in round 2 to the corresponding userid.
function RoundTwoEnd() {
    $settingsDAO = new SettingsDAO();
    $bidDAO = new BidDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();

    $allSections = $sectionDAO->getAllSections();
    foreach ($allSections as $aSection) {
        $course = $aSection->getCourse();
        $section = $aSection->getSection();
        $bidsToRefund = $bidDAO->retrieveBidBySectionForRound($course,$section,"F",2);
        foreach ($bidsToRefund as $aBidToRefund) {
            $userid = $aBidToRefund->getUserid();
            $amount = $aBidToRefund->getAmount();
            $newBalance = $amount + $studentDAO->retrieveByUserID($userid)->getEdollar();
            $studentDAO->updateBalance($userid, $newBalance);
        }
    }

}

?> 