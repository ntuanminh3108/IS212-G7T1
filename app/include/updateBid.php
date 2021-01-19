<?php
require_once 'common.php';
require_once 'RoundClearing.php';

function updateBid($userid, $amount, $course, $section) {

    # Initializing variables needed for error handling 
    $errors = array();

    # Initialize all the needed DAOs
    $courseDAO = new CourseDAO();
    $sectionDAO = new SectionDAO();
    $studentDAO = new StudentDAO();
    $prerequisiteDAO = new PrerequisiteDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $bidDAO = new BidDAO();
    $settingsDAO = new SettingsDAO();

    # Handling possible section errors
    $section = strtoupper($section);
    $course = strtoupper($course);

    # ======================= #
    #     INPUT VALIDATION    #
    # ======================= #

    # == Check for invalid amount == #
    # Explode the amount variable 
    if (is_numeric($amount)){
        $index = stripos($amount,".");
        # If a dot exist within the amount variable
        if ($index != -1){
            $amount_array = explode(".", $amount);
            # Check if the decimal area ia correct
            if (count($amount_array)> 1 && strlen($amount_array[1]) > 2) {
                $errors[] = "invalid amount";
            } else {
                # If correct, check if the amount is above 10
                if ($amount < 10) {
                    $errors[] = "invalid amount";
                } 
            }
        }
    }

        # == Check for invalid  course == #
        if($courseDAO->retrieveByCourse($course) == []) {
            # If an empty array is returned, it means that the course code referenced does not exist in the course table
            $errors[] = "invalid course";
        } else {
            # If the course exists within the database
            # Check if section number referenced within this line exists within the section database 
            $section_check = $sectionDAO->retrieveOnlySectionByCourse($course);
            if(!in_array($section, $section_check)) {
                # If an empty array is returned, it means that the course code referenced does not exist in the course table
                $errors[] = "invalid section";
            }
        }

    # == Check for invalid userid == #
    # If null is returned by the retrieveByUserID function then the userid does not exist within the database 
    if ($studentDAO->retrieveByUserID($userid) == null) {
        $errors[] = "invalid userid";
    }

    # If input validation fails, do not proceed with the logic validation 
    if (count($errors) > 0) {
        # Generate the error message 
        $result = [ 
            "status" => "error",
            "message" => $errors
        ];
    } else {

        # ======================= #
        #     LOGIC VALIDATION    #
        # ======================= #

        # Object creation
        $courseObj = $courseDAO->retrieveByCourse($course);
        $sectionObj = $sectionDAO->retrieveBySectionAndCourse($course, $section);
        $prerequisiteObj = $prerequisiteDAO->retrieve($course);

        # Check for bid too low 
        # Get the current round number (0,1,2)
        $round_number = $settingsDAO->getRoundNumber();
        if ($round_number == 2) {
            # Check if the amount bidded with is less than the section's minBid 
            if ($amount < $sectionObj->getMinBid()) {
                $errors[] = "bid too low";
            }
        }

        # == Check for insufficient e$ == #
        $studentObj = $studentDAO->retrieveByUserID($userid);
        $current_wallet_amount = $studentObj->getEdollar();
        if ($bidDAO->retrieveBidByUserIDAndCourseAndRound($userid, $course, $round_number) == null) {
            # The bid does not exist, so it will be added if all validations pass successfully
            # Find out with the student's current wallet amount, if he/she has enough money to place the bid (if amount to be placed is more than the current wallet amount, generate error message)
            if ($amount > $current_wallet_amount) {
                $errors[] = "insufficient e$";
            }

        } else {
            # The bid already exist, so it will be updated if all validations pass successfully
            # Obtain the original bid object 
            $bidObj = $bidDAO->retrieveBidByUserIDAndCourseAndRound($userid, $course, $round_number);

            # Since the bids during round 2 is either a success or fail --> amount is dynamically returned to the user 
            if ($round_number == 2) {
                if ($amount > $current_wallet_amount) {
                    $errors[] = "insufficient e$";
                }
            } else { # Round 1 puts amount bidded on hold there is a need to add the original bidded amount back to the user 
                # Get the original bid amount 
                $origBid = $bidObj->getAmount();
                # Add it back with the student's current wallet amount before check if he/she has enough to place the bid 
                $current_with_origBid = $current_wallet_amount + $origBid;
                if ($amount > $current_with_origBid) {
                    $errors[] = "insufficient e$";
                }
            }

        }

        # == Check if timetable clashes == # 
        # Retrieve all the bids that the student currently has 
        $student_bids = $bidDAO->retrieve($userid);
        # Get the section object of the course that is being bidded for 
        $sectionObj = $sectionDAO->retrieveBySectionAndCourse($course, $section);
        # Get the day (1-7) which the section will be held on 
        $current_bid_section_day = $sectionObj->getDay();
        # Create a variable to keep track whether or not there has been a clash in the timetable 
        $timetable_clash = False;
        foreach ($student_bids as $a_student_bid) {
            if (isset($bidObj)) {
                # Process the bids and make sure that (1) if it is an update, ignore the bid object that already exists within the database and (2) the bid is either a successful bid or a pending bid 
                if ($a_student_bid <> $bidObj && $a_student_bid->getStatus() != "F") {

                    # For every bid ...
                    # Get the course and section of the existing bid 
                    $existing_bid_course = $a_student_bid->getCourse();
                    $existing_bid_section = $a_student_bid->getSection();

                    # Get the section object of the existing bid
                    $eSectionObj= $sectionDAO->retrieveBySectionAndCourse($existing_bid_course, $existing_bid_section);
                    # Get the day (1-7) which the existing bid's section will be held on 
                    $existing_bid_section_day = $eSectionObj->getDay();

                    # If bidObj's day is the same as the eSectionObj's day
                    if ($current_bid_section_day == $existing_bid_section_day) {
                        # Get the timing of the existing bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $existing_bid_section_start = implode("", explode(":", $eSectionObj->getStart()));
                        $existing_bid_section_end = implode("", explode(":", $eSectionObj->getEnd()));

                        # Get the timing of the current bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $current_bid_section_start = implode("", explode(":", $sectionObj->getStart()));
                        $current_bid_section_end = implode("", explode(":", $sectionObj->getEnd()));

                        # Check if the current bid start time is more than the existing bid start time and less than the existing bid end time. Refer to the bottom for an example
                        # - | --------------------------- | -
                        # start                          end
                        # 15:30                         18:45
                        #    <----- btwn this range?------>
                        # OR Check if the current bid end time is more than the existing bid start time and less than the existing bid end time
                        if (($existing_bid_section_start <= $current_bid_section_start && $current_bid_section_start < $existing_bid_section_end) || ($existing_bid_section_start < $current_bid_section_end && $current_bid_section_end <= $existing_bid_section_end)) {
                            # If so, that means that there is a timetable clash 
                            $timetable_clash = True;
                        }
                    }
                }
            } else {
                # Process the bids and make sure that the bid is either a successful bid or a pending bid 
                if ($a_student_bid->getStatus() != "F") {

                    # For every bid ...
                    # Get the course and section of the existing bid 
                    $existing_bid_course = $a_student_bid->getCourse();
                    $existing_bid_section = $a_student_bid->getSection();

                    # Get the section object of the existing bid
                    $eSectionObj= $sectionDAO->retrieveBySectionAndCourse($existing_bid_course, $existing_bid_section);
                    # Get the day (1-7) which the existing bid's section will be held on 
                    $existing_bid_section_day = $eSectionObj->getDay();

                    # If bidObj's day is the same as the eSectionObj's day
                    if ($current_bid_section_day == $existing_bid_section_day) {
                        # Get the timing of the existing bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $existing_bid_section_start = implode("", explode(":", $eSectionObj->getStart()));
                        $existing_bid_section_end = implode("", explode(":", $eSectionObj->getEnd()));

                        # Get the timing of the current bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $current_bid_section_start = implode("", explode(":", $sectionObj->getStart()));
                        $current_bid_section_end = implode("", explode(":", $sectionObj->getEnd()));

                        # Check if the current bid start time is more than the existing bid start time and less than the existing bid end time. Refer to the bottom for an example
                        # - | --------------------------- | -
                        # start                          end
                        # 15:30                         18:45
                        #    <----- btwn this range?------>
                        # OR Check if the current bid end time is more than the existing bid start time and less than the existing bid end time
                        if (($existing_bid_section_start <= $current_bid_section_start && $current_bid_section_start < $existing_bid_section_end) || ($existing_bid_section_start < $current_bid_section_end && $current_bid_section_end <= $existing_bid_section_end)) {
                            # If so, that means that there is a timetable clash 
                            $timetable_clash = True;
                        }
                    }
                }
            }
        }

        # If the timetable clash variable was set to true 
        if ($timetable_clash == True) {
            # Generate error message 
            $errors[] = 'class timetable clash';
        }

        
        # == Check if already enrolled in course == #
        if ($bidDAO->getBidByUserIDCourseStatus($userid, $course,"S") != null && $round_number <>$bidDAO->getBidByUserIDCourseStatus($userid, $course,"S")->getRound()) {
            $errors[] = 'course enrolled';
        }

        # == Check if exam timetable clashes == #
        # Generate a course object 
        $courseObj = $courseDAO->retrieveByCourse($course);
        # Get the exam date of the bid currently being made/updated 
        $current_bid_exam_date = $courseObj->getExamDate();
        # Create a variable to keep track whether or not there has been a clash in the exam timetable  
        $exam_clash = False;
        foreach ($student_bids as $a_student_bid) {
            # Process the bids and make sure that (1) if it is an update, ignore the bid object that already exists within the database and (2) the bid is either a successful bid or a pending bid 
            if(isset($bidObj)) {
                if ($a_student_bid <> $bidObj && $a_student_bid->getStatus() != "F") {

                    # Get the existing bid's course object 
                    $eCourseObj = $courseDAO->retrieveByCourse($a_student_bid->getCourse());
                    # Using the course object, get the exam date 
                    $existing_bid_exam_date = $eCourseObj->getExamDate();
                    
                    # If the current bid and the existing bid shares the same exam date, check if their timing clashes 
                    if ($current_bid_exam_date == $existing_bid_exam_date) {
    
                        # Get the timing of the existing bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $existing_bid_exam_start = implode("",explode(":", $eCourseObj->getExamStart()));
                        $existing_bid_exam_end = implode("",explode(":", $eCourseObj->getExamEnd()));
    
                        # Get the timing of the current bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $current_bid_exam_start = implode("",explode(":", $courseObj->getExamStart()));
                        $current_bid_exam_end = implode("",explode(":", $courseObj->getExamEnd()));
    
                        # Check if the current bid start time is more than the existing bid start time and less than the existing bid end time. Refer to the bottom for an example
                        # - | --------------------------- | -
                        # start                          end
                        # 15:30                         18:45
                        #    <----- btwn this range?------>
                        # OR Check if the current bid end time is more than the existing bid start time and less than the existing bid end time
                        if (($existing_bid_exam_start <= $current_bid_exam_start && $current_bid_exam_start < $existing_bid_exam_end) || ($existing_bid_exam_start < $current_bid_exam_end && $current_bid_exam_end <= $existing_bid_exam_end)) {
                            # If so, that means that there is a timetable clash 
                            $exam_clash = True;
                        }
                    }
                }
            } else {
                # Process the bids and make sure that the bid is either a successful bid or a pending bid 
                if ($a_student_bid->getStatus() != "F") {

                    # Get the existing bid's course object 
                    $eCourseObj = $courseDAO->retrieveByCourse($a_student_bid->getCourse());
                    # Using the course object, get the exam date 
                    $existing_bid_exam_date = $eCourseObj->getExamDate();
                    
                    # If the current bid and the existing bid shares the same exam date, check if their timing clashes 
                    if ($current_bid_exam_date == $existing_bid_exam_date) {
    
                        # Get the timing of the existing bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $existing_bid_exam_start = implode("",explode(":", $eCourseObj->getExamStart()));
                        $existing_bid_exam_end = implode("",explode(":", $eCourseObj->getExamEnd()));
    
                        # Get the timing of the current bid without the semicolon (meaning to say that if the timing is 15:30, explode it to ['15', '30'] and then implode to join it back together ('1530'))
                        $current_bid_exam_start = implode("",explode(":", $courseObj->getExamStart()));
                        $current_bid_exam_end = implode("",explode(":", $courseObj->getExamEnd()));
    
                        # Check if the current bid start time is more than the existing bid start time and less than the existing bid end time. Refer to the bottom for an example
                        # - | --------------------------- | -
                        # start                          end
                        # 15:30                         18:45
                        #    <----- btwn this range?------>
                        # OR Check if the current bid end time is more than the existing bid start time and less than the existing bid end time
                        if (($existing_bid_exam_start <= $current_bid_exam_start && $current_bid_exam_start < $existing_bid_exam_end) || ($existing_bid_exam_start < $current_bid_exam_end && $current_bid_exam_end <= $existing_bid_exam_end)) {
                            # If so, that means that there is a timetable clash 
                            $exam_clash = True;
                        }
                    }
                }
            }
        }

        # If the timetable clash variable was set to true 
        if ($exam_clash == True) {
            # Generate error message 
            $errors[] = 'exam timetable clash';
        }

        # == Check for incomplete prerequisites == #
        # Get all the prerequisite needed for the course 
        $prerequisiteObj = $prerequisiteDAO->retrieve($course);
        # If an empty array is returned, it means that the course does not have any prerequisite
        if ($prerequisiteObj <> []) {
            # If there are prerequistes, traverse through it 
            $prerequisites_required = $prerequisiteDAO->retrieve($course);
            foreach ($prerequisites_required as $a_prerequisite_required) {
                # Get the prerequisite course code from it's object 
                $prerequisite_to_check = $a_prerequisite_required->getPrerequisite();
                # Check whether the student has completed the course 
                if ($courseCompletedDAO->getStudentComplete($userid, $prerequisite_to_check) == null) {
                    # Once it is found that a prerequisite has not been attempted, break out of the for loop to prevent repeating the prerequisite 
                    $errors[] = 'incomplete prerequisites';
                    break;
                }
            }
        }

        # == Check for if the round active for bidding == #
        # Get the active round status from the database (either 1 (active round) or 0(inactive round))
        $biddingAllowed = $settingsDAO->getBiddingAllowed();
        if ($biddingAllowed == 0) {
            $errors[] = 'round ended';
        }

        # == Checks if course already completed the course == #
        if ($courseCompletedDAO->getStudentComplete($userid, $course) != null) {
            $errors[] = 'course completed';
        }

        # == Checks if section limit reached == #
        # If the bid does not exist yet, check if the student has already bidded for 5 sections 
        # If the bid already exist, there is no need to check for this 
        if ($round_number == 2) {
            $section_check = 0;
            foreach ($student_bids as $a_student_bid) {
                if ($a_student_bid->getStatus() == "S") {
                    $section_check += 1;
                }
            }
            if (($section_check + 1) > 5) {
                $errors[] = "section limit reached";
            }
        } else {
            if (!isset($bidObj)) {
                if (count($student_bids) >= 5) {
                    $errors[] = "section limit reached";
                }
            }
        }

        # == Check if not own school == #
        # During round 1, the student can only bid for courses provided by his/her own school
        # If the round number is 1
        if ($round_number == 1) {
            # Get which school the student is from 
            $student_school = $studentObj->getSchool();
            # Get which school is providing the course that the student wants to bid for 
            $course_school = $courseObj->getSchool();
            if ($student_school <> $course_school) {
                $errors[] = 'not own school course';
            }
        }

        # == Check if no vacancy == #
        if($sectionObj->getVacancy() <= 0 && (count($bidDAO->retrieveBidBySectionForRound($course,$section,'S',1)) == $sectionObj->getSize())) {
            $errors[] = 'no vacancy';
        }

        # If there are errors, generate and return an error message 
        if (count($errors) != 0) {
            $result = [ 
                "status" => "error",
                "message" => $errors
            ];
        } else {

            # Check which round is it currently 
            if ($round_number == 2) {
              
                # Add the bid and deduct
                $bidDAO->removeBid($userid, $course);
                $bidDAO->add($userid, $amount, $course, $section, "S", $round_number);
                $studentDAO->updateBalance($userid, ($current_wallet_amount - $amount));

                # Call RoundTwoClearing Function to reprocess the bids
                $minClearingPrice = RoundTwoClearing($course, $section);

                # Update minimum clearing price
                if ($minClearingPrice + 1 > $sectionObj->getMinBid()) {
                    $sectionDAO->updateMinBid($course, $section, $minClearingPrice); // + 1 to min price
                }

            } else {
                # Deduct e$ from student
                if (isset($bidObj)) {
                    $bidded_amount = $bidObj->getAmount();
                    $studentDAO->updateBalance($userid, ($current_wallet_amount+$bidded_amount)-$amount);
                } else {
                    $studentDAO->updateBalance($userid, ($current_wallet_amount - $amount));
                }

                # Add the bid
                if (isset($bidObj)) {
                    # Delete existing bids
                    $bidDAO->removeBid($userid, $course);
                    # Add the updated bid
                    $bidDAO->add($userid, $amount, $course, $section, "P", $round_number);
                } else {
                    $bidDAO->add($userid, $amount, $course, $section, "P", $round_number);
                }

            }

            $result = [ 
                "status" => "success",
            ];
        }

    }

    # Return the success/error message and end the file processing here 
    return $result;
    exit; 
    
}