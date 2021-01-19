<?php
    require_once 'common.php';

    function doBootstrap() {

        $errors = array();

        # Temporary name of the zipped file uploaded onto the apache server 
        $zip_file = $_FILES["bootstrap-file"]["tmp_name"];

        # Get the directory used by PHP for temporary files 
        $temp_dir = sys_get_temp_dir();

        # Keep track of the number of lines successfully validated for each file 
        $course_validated = 0;
        $section_validated = 0;
        $student_validated = 0;
        $prerequisite_validated = 0;
        $course_completed_validated = 0;
        $bid_validated = 0;

        # Check the size of the uploaded zip folder to see if it has been successfully uploaded 
        if ($_FILES["bootstrap-file"]["size"] <= 0) {
            # Unsuccessfully uploaded: SIZE == 0
            $errors[] = "missing files";
        } else {
            # Successfully uploaded: SIZE > 0 
            # Step 1: Create a new ZipArchive class
            $zip = new ZipArchive;
            # Step 2: Use the open method that the class provide to open the zip file archive 
            $res = $zip->open($zip_file);
            # Step 3: Check if the file has been successfully opened 
            if ($res === TRUE) {
                # Step 4: Extract the opened file to the directory used by PHP for temporary files
                # This step is under the impression that the directory of the zip file is:
                # zipped folder
                # | - course.csv
                # | - section.csv
                # | - student.csv
                # | - prerequisite.csv
                # | - course_completed.csv
                # | - bid.csv
                # THERE IS NO ADDITIONAL ROOT FOLDER WITHIN THE ZIPPED FOLDER 

                # Check for missing files 
                // if ($zip->locateName('course.csv') === FALSE) {
                //     $error = "missing course.csv";
                //     array_push($errors, $error);

                // }

                // if ($zip->locateName('section.csv') === FALSE) {
                //     $error = "missing section.csv";
                //     array_push($errors, $error);
                // }

                // if ($zip->locateName('student.csv') === FALSE) {
                //     $error = "missing student.csv";
                //     array_push($errors, $error);
                // }

                // if ($zip->locateName('prerequisite.csv') === FALSE) {
                //     $error = "missing prerequisite.csv";
                //     array_push($errors, $error);
                // }

                // if ($zip->locateName('course_completed.csv') === FALSE) {
                //     $error = "missing course_completed.csv";
                //     array_push($errors, $error);
                // }

                // if ($zip->locateName('bid.csv') === FALSE) {
                //     $error = "missing bid.csv";
                //     array_push($errors, $error);
                // }

                // if (count($errors) > 0) {
                //     $_SESSION['errors'] = $errors;
                //     header ("Location: admin_homepage.php");
                //     exit;
                // }

                $zip->extractTo($temp_dir);

                # Step 5: Close the active archive 
                $zip->close();

                # Step 6: Initialized all the locations for the extracted .csv files within the temporary directory 
                $course_path = "$temp_dir/course.csv";
                $section_path = "$temp_dir/section.csv";
                $student_path = "$temp_dir/student.csv";
                $prerequisite_path = "$temp_dir/prerequisite.csv";
                $course_completed_path = "$temp_dir/course_completed.csv";
                $bid_path = "$temp_dir/bid.csv";

                # Step 7: Open the files using fopen function (NOTE: The @ before the function surpresses error messages)
                # @ supresses the error message 
                $course = @fopen($course_path, "r");
                $section = @fopen($section_path, "r");
                $student = @fopen($student_path, "r");
                $prerequisite = @fopen($prerequisite_path, "r");
                $course_completed = @fopen($course_completed_path, "r");
                $bid = @fopen($bid_path, "r");

                # Step 8: Check if the files exist within the path that you have specified 
                if (empty($course)  || empty($section) || empty($student) || empty($prerequisite) || empty($course_completed) || empty($bid)){
                    # Insert an error message into the error array 
                    $errors[] = "missing files";

                    # For the remaining files that are found within the path specified, unlink them (delete the files within the server's temporary directory)
                    # Unlinking course file, if exist 
    				if (!empty($course)){
    					fclose($course);
    					@unlink($course_path);
    				} 
                    
                    # Unlinking section file, if exist 
    				if (!empty($section)) {
    					fclose($section);
    					@unlink($section_path);
    				}
                    
                    # Unlinking student file, if exist 
    				if (!empty($student)) {
    					fclose($student);
    					@unlink($student_path);
                    }
                    
                    # Unlinking prerequisite file, if exist 
    				if (!empty($prerequisite)) {
    					fclose($prerequisite);
    					@unlink($prerequisite_path);
                    }
                    
                    # Unlinking course_completed file, if exist 
    				if (!empty($course_completed)) {
    					fclose($course_completed);
    					@unlink($course_completed_path);
                    }
                    
                    # Unlinking bid file, if exist 
    				if (!empty($bid)) {
    					fclose($bid);
    					@unlink($bid_path);
                    }
                    	
                } else {

                    # ================================== VALIDATION START ========================================

                    # If all the necessary files exist, 
                    # Step 9a: Truncate all the databases 
                    # For insertion into database
                    $courseDAO = new CourseDAO();
    				$sectionDAO = new SectionDAO();
    				$studentDAO = new StudentDAO();
    				$prerequisiteDAO = new PrerequisiteDAO();
    				$courseCompletedDAO = new CourseCompletedDAO();
                    $bidDAO = new BidDAO();
                    
                    # Remove existing data within the 6 tables 
                    $bidDAO->removeAll();
                    $courseCompletedDAO->removeAll();
                    $prerequisiteDAO->removeAll();
                    $studentDAO->removeAll();
                    $sectionDAO->removeAll();
                    $courseDAO->removeAll();

                    # Step 10: Parse through the files and validate line by line 
                    # ------------ #
                    #     COURSE   #
                    # ------------ #
                    # Start from the course.csv file 
                    $data_course = fgetcsv($course); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines
                    # This while loop will stop once all the lines of the course.csv is validated (file pointer points to a non-existent line and returns a FALSE value)
                    $row  = 2; 
                    $filename = "course.csv";
                    while (($data_course = fgetcsv($course)) != FALSE) {
                        /*
                            Course format: 
                                $data_course[0] - course
                                $data_course[1] - school
                                $data_course[2] - title
                                $data_course[3] - description
                                $data_course[4] - exam_date
                                $data_course[5] - exam_start
                                $data_course[6] - exam_end
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields and remove whitespaces from the front and end of the field 
                        $course_code = trim($data_course[0]);
                        $school = trim($data_course[1]);
                        $title = trim($data_course[2]);
                        $description = trim($data_course[3]);
                        $exam_date = trim($data_course[4]);
                        $exam_start = trim($data_course[5]);
                        $exam_end = trim($data_course[6]);

                        # Check if any of the fields are empty
                        # We use strlen() here instead of empty() because empty recognises certain values as empty. For example, a field with the number 0 is considered to be empty. By using trim() to remove the whitespaces, you can use strlen() to check for the length of the field. A field with 0 would no longer be recognised as an empty field. 
                        # Set a $check boolean var to be True, whenever a check fails, change to False.
                        $check = True;
                        if (strlen($course_code) == 0) {
                            $row_errors[] = "blank course";
                            $check = False;
                        }

                        if (strlen($school) == 0) {
                            $row_errors[] = "blank school";
                            $check = False;
                        }
  
                        if (strlen($title) == 0) {
                            $row_errors[] = "blank title";
                            $check = False;
                        }

                        if (strlen($description) == 0) {
                            $row_errors[] = "blank description";
                            $check = False;
                        }

                        if (strlen($exam_date) == 0) {
                            $row_errors[] = "blank exam date";
                            $check = False;
                        }

                        if (strlen($exam_start) == 0) {
                            $row_errors[] = "blank exam start";
                            $check = False;
                        }

                        if (strlen($exam_end) == 0) {
                            $row_errors[] = "blank exam end";
                            $check = False;
                        }

                        # Checks if any field is blank, skip the remaining validation
                        if ($check) {
                            # ============================================================================ #
                            # Check if the exam date is in the correct format (using regular expressions)  #
                            # ^ identifies the start and $ identifies the end                              #
                            # Split the regular expression for better understanding                        #
                            # ============================================================================ #
                            /*
                                Year: [0-9]{4} means to accept any four consective digits from range 0-9
                                Month: (0[1-9]|1[0-2]) means to accept 0[1 to 9] --> 01 to 09 OR 1[0 to 2] --> 10 to 12
                                Day: (0[1-9]|[1-2][0-9]|3[0-1]) means to accept 0[1 to 9] --> 01 to 09 OR [1 to 2][0 to 9] --> 10 to 29 OR [0  to 1] --> 30 to 31
                            */

                            # For $exam_date
                            if (!preg_match("/^[0-9]{4}(0[0-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])$/", $exam_date)) {
                                // Exam date is in the wrong format (insert into the errors array in the following error: filename, row number and error messages)
                                $row_errors[] = "invalid exam date";
                                $check = False;
                            }

                            if ($check && strlen($exam_date) == 8) {
                                $yearCheck = intval(substr($exam_date,0,4));
                                $monthCheck = intval(substr($exam_date,4,2));
                                $dayCheck = intval(substr($exam_date,6,2));
                                if (!checkdate($monthCheck,$dayCheck,$yearCheck)) {
                                    $row_errors[] = "invalid exam date";
                                    $check = False;
                                }
                            }

                            # ========================================================================================= #
                            # Check if the exam start and exam end is in the correct format (using regular expressions) #
                            # ^ identifies the start and $ identifies the end                                           #
                            # Split the regular expression for better understanding                                     #
                            # ========================================================================================= #
                            /*
                                Specified format: H:MM
                                Hour: (00|[1-9]|1[1-9]|2[0-3]) accepts 12am(00), 1am(1) to 9am,(9) 11am(11) to 7pm(19) and 8pm(20) to 11pm(23)
                                Minute: [0-5][0-9] accepts 00 to 59
                            */

                            # For $exam_start
                            if (!preg_match("/^(00|[1-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $exam_start)) {
                                // Exam start time is in the wrong format (insert into the errors array in the following error: filename, row number and error messages)
                                $row_errors[] = "invalid exam start";
                                $check = False;
                            } 

                            # For $exam_end
                            if (!preg_match("/^(00|[1-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $exam_end)) {
                                // Exam start time is in the wrong format (insert into the errors array in the following error: filename, row number and error messages)
                                $row_errors[] = "invalid exam end";
                                $check = False;
                            }

                            # If the exam timings are in the correct format, check if the end time is after the start time
                            if ($check) {
                                # Explode the string to obtain only the numbers 
                                $exam_start_array = explode(":", $exam_start);
                                $exam_end_array = explode(":", $exam_end);

                                # Concatenate the strings 
                                $exam_start_str = implode("",$exam_start_array);
                                $exam_end_str = implode("",$exam_end_array);
                                
                                # Compare the numbers (it is possible to do it like this because the timing is in 24hr format)
                                if ($exam_end_str < $exam_start_str) {
                                    $row_errors[] = "invalid exam end";
                                    $check = False;
                                }
                            }

                            # Check if the title field exceeds 100 characters 
                            if (strlen($title) > 100) {
                                $row_errors[] = "invalid title";
                                $check = False;
                            }

                            # Check if the title field exceeds 100 characters
                            if (strlen($description) > 1000) {
                                $row_errors[] = "invalid description";
                                $check = False;
                            }

                            # If there are no errors during validation, insert into the database 
                            if ($check) {
                                # Remove any invalid or hidden characters -- FOR MAC -- #
                                $course_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $course_code);
                                $school = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $school);
                                $title = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $title);
                                $description = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $description);
                                $exam_date = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $exam_date);
                                $exam_start = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $exam_start);
                                $exam_end = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $exam_end);

                                $courseDAO->add($course_code, $school, $title, $description, $exam_date, $exam_start, $exam_end);
                                $course_validated += 1;
                            }
                        }

                        # Insert error msgs
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        } 

                        # Increment the row counter 
                        $row += 1;
                    }

                    # ------------ #
                    #    SECTION   #
                    # ------------ #
                    # Start from the section.csv file 
                    $data_course = fgetcsv($section); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines (insert the said lines until they fulfil all the validations)
                    $row  = 2;
                    $filename = "section.csv";
                    while (($data_course = fgetcsv($section)) != FALSE) {
                        /*
                            Section format: 
                                $data_course[0] - course
                                $data_course[1] - section
                                $data_course[2] - day
                                $data_course[3] - start
                                $data_course[4] - end
                                $data_course[5] - instructor 
                                $data_course[6] - venue
                                $data_course[7] - size
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields 
                        $course_code = trim($data_course[0]); 
                        $section_no = trim($data_course[1]); 
                        $day = trim($data_course[2]); 
                        $start = trim($data_course[3]);
                        $end = trim($data_course[4]);
                        $instructor = trim($data_course[5]);
                        $venue = trim($data_course[6]);
                        $size = trim($data_course[7]); 

                        # Check if any of the fields are empty
                        # We use strlen() here instead of empty() because empty recognises certain values as empty. For example, a field with the number 0 is considered to be empty. By using trim() to remove the whitespaces, you can use strlen() to check for the length of the field. A field with 0 would no longer be recognised as an empty field. 
                        $check = True;
                        if (strlen($course_code) == 0) {
                            $row_errors[] = "blank course";
                            $check = False;
                        }

                        if (strlen($section_no) == 0) {
                            $row_errors[] = "blank section";
                            $check = False;
                        }

                        if (strlen($day) == 0) {
                            $row_errors[] = "blank day";
                            $check = False;
                        }

                        if (strlen($start) == 0) {
                            $row_errors[] = "blank start";
                            $check = False;
                        }

                        if (strlen($end) == 0) {
                            $row_errors[] = "blank end";
                            $check = False;
                        }

                        if (strlen($instructor) == 0) {
                            $row_errors[] = "blank instructor";
                            $check = False;
                        }

                        if (strlen($venue) == 0) {
                            $row_errors[] = "blank venue";
                            $check = False;
                        }

                        if (strlen($size) == 0) {
                            $row_errors[] = "blank size";
                            $check = False;
                        }

                        # Checks if any field is blank, skip the remaining validation
                        if ($check) {
                            # Check if course code referenced within this line exists within the course database 
                            if($courseDAO->retrieveByCourse($course_code) == null) {
                                # If an empty array is returned, it means that the course code referenced does not exist in the course table
                                $row_errors[] = "invalid course";
                                $check = False;
                            }
                        }
                        if ($check) {
                            # Check if the section field is the correct format (S1, S2, ... S99)
                            if (strlen($section_no)==2){
                                if (!preg_match("/^S([1-9]?)$/", $section_no)) {
                                    $row_errors[] = "invalid section";
                                    $check = False;
                                } 
                            }
                            else {
                                if (!preg_match("/^S([1-9][1-9]?)$/", $section_no)) {
                                    $row_errors[] = "invalid section";
                                    $check = False;
                                } 
                            }


                            # Check if the day field is in the correct format (1-7)
                            $day_array = explode(".", $day);
                            if (count($day_array) > 1) {
                                $row_errors[] = "invalid day";
                                $check = False;
                            } else {
                                # Double check - in case they input a sentence with a period (.)
                                if (!is_numeric($day_array[0]) || $day_array[0] < 1 || $day_array[0] > 7) {
                                    $row_errors[] = "invalid day";
                                    $check = False;
                                }
                            }

                            # ========================================================================================= #
                            # Check if the start and end is in the correct format (using regular expressions) #
                            # ^ identifies the start and $ identifies the end                                           #
                            # Split the regular expression for better understanding                                     #
                            # ========================================================================================= #
                            /*
                                Specified format: H:MM
                                Hour: (00|[1-9]|1[1-9]|2[0-3]) accepts 12am(00), 1am(1) to 9am,(9) 11am(11) to 7pm(19) and 8pm(20) to 11pm(23)
                                Minute: [0-5][0-9] accepts 00 to 59
                            */
                            # For $start
                            if (!preg_match("/^(00|[1-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $start)) {
                                $row_errors[] = "invalid start";
                                $check = False;
                            } 

                            # For $end
                            if (!preg_match("/^(00|[1-9]|1[0-9]|2[0-3]):[0-5][0-9]$/", $end)) {
                                $row_errors[] = "invalid end";
                                $check = False;
                            }

                            if ($check) {
                                $start_exploded_imploded = implode("", explode(":", $start));
                                $end_exploded_imploded = implode("", explode(":", $end));
                                if ($end_exploded_imploded < $start_exploded_imploded) {
                                    $row_errors[] = "invalid end";
                                    $check = False;
                                }
                            }

                            # Check if the instructor field is in the correct format 
                            if (strlen($instructor) > 100) {
                                $row_errors[] = "invalid instructor";
                                $check = False;
                            }

                            # Check if the venue field is in the correct format 
                            if (strlen($venue) > 100) {
                                $row_errors[] = "invalid venue";
                                $check = False;
                            }

                            # Check if the size field is in the correct format 
                            if (!is_numeric($size) or $size<=0){
                                $row_errors[] = "invalid size";
                                $check = False;
                            }

                            # If there are no errors during validation, insert record into the database
                            if ($check) {

                                # Convert day and size to integers for insertion into database 
                                $day = (int) $day;
                                $size = (int) $size;

                                # Remove any invalid or hidden characters -- FOR MAC
                                $course_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $course_code);
                                $section_no = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $section_no);
                                $day = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $day);
                                $start = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $start);
                                $end = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $end);
                                $instructor = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $instructor);
                                $venue = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $venue);
                                $size = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $size);
                                $minBid = 10.00;
                                $vacancy = $size;
                                
                                $sectionDAO->add($course_code, $section_no, $day, $start, $end, $instructor, $venue, $size, $minBid, $vacancy);
                                $section_validated += 1;
                            }   
                        }

                        # Insert error msgs 
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        } 

                        # Increment the row counter 
                        $row += 1;
                    }

                    # ------------ #
                    #    STUDENT   #
                    # ------------ #
                    # Start from the student.csv file 
                    $data_course = fgetcsv($student); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines (insert the said lines until they fulfil all the validations)
                    $row  = 2; 
                    $filename = "student.csv";
                    while (($data_course = fgetcsv($student)) != FALSE) {
                        /*
                            Student format: 
                                $data_course[0] - userid
                                $data_course[1] - password
                                $data_course[2] - name
                                $data_course[3] - school
                                $data_course[4] - edollar
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields 
                        $userid = trim($data_course[0]);
                        $password = trim($data_course[1]);
                        $name = trim($data_course[2]);
                        $school = trim($data_course[3]);
                        $edollar = trim($data_course[4]);

                        # Check if any of the fields are empty
                        $check = True;
                        if (strlen($userid) == 0) {
                            $row_errors[] = "blank userid";
                            $check = False;
                        }

                        if (strlen($password) == 0) {
                            $row_errors[] = "blank password";
                            $check = False;
                        }

                        if (strlen($name) == 0) {
                            $row_errors[] = "blank name";
                            $check = False;
                        }

                        if (strlen($school) == 0) {
                            $row_errors[] = "blank school";
                            $check = False;
                        }

                        if (strlen($edollar) == 0) {
                            $row_errors[] = "blank e-dollar";
                            $check = False;
                        }

                        # Checks if any field is blank, skip the remaining validation
                        if ($check) {
                            # Check if the userid field is in the correct format 
                            if (strlen($userid) > 128) {
                                $row_errors[] = "invalid userid";
                                $check = False;
                            }

                            # Check if the userid already exists within the database 
                            if (!($studentDAO->retrieveByUserID($userid) == null)){
                                $row_errors[] = "duplicate userid";
                                $check = False;
                            }

                            # Check if edollar field is in the right format 
                            $edollar_array = explode(".", $edollar);
                            if (count($edollar_array) > 2) {
                                $row_errors[] = "invalid e-dollar";
                                $check = False;
                            } else {
                                # If the edollar field is a float 
                                if (count($edollar_array) == 2) {
                                    # Check if the decimal place is a numeric value and has 2 decimal place
                                    if (!is_numeric($edollar_array[1])  || strlen($edollar_array[1]) > 2) {
                                        $row_errors[] = "invalid e-dollar";
                                        $check = False;
                                    } 
                                } 

                                # Check if the number field is a numeric value
                                if (!is_numeric($edollar_array[0])) {
                                    $row_errors[] = "invalid e-dollar";
                                    $check = False;
                                }
                            }

                            # Check if the password field is in the right format 
                            if (strlen($password) > 128) {
                                $row_errors[] = "invalid password";
                                $check = False;
                            }

                            # Check if the name field is in the right format 
                            if (strlen($name) > 100) {
                                $row_errors[] = "invalid name";
                                $check = False;
                            }

                            # If there are no errors during validation, insert record into the database
                            if ($check) {

                                # Convert the edollar to a float for insertion into database
                                $edollar = (float) $edollar;

                                // Remove any invalid or hidden characters -- FOR MAC
                                $userid = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $userid);
                                $password = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $password);
                                $name = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $name);
                                $school = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $school);
                                $edollar = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $edollar);

                            $studentDAO->add($userid, $password, $name, $school, $edollar);
                            $student_validated += 1;
                        }
                    }

                        # Insert error msgs 
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        } 

                        # Increment the row counter 
                        $row += 1;
                    }

                    # ----------------- #
                    #    PREREQUISITE   #
                    # ----------------- #
                    # Start from the prerequisite.csv file 
                    $data_course = fgetcsv($prerequisite); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines (insert the said lines until they fulfil all the validations)
                    $row  = 2; # TO DO: DECIDE WHETHER TO USE 1 OR 2
                    $filename = "prerequisite.csv";
                    while (($data_course = fgetcsv($prerequisite)) != FALSE) {
                        /*
                            Prerequisite format: 
                                $data_course[0] - course
                                $data_course[1] - prerequisite
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields 
                        $course_code = trim($data_course[0]);
                        $prerequisite_code = trim($data_course[1]);
                        
                        # Check if any of the fields are empty
                        $check = True;
                        if (strlen($course_code) == 0) {
                            $row_errors[] = "blank course";
                            $check = False;
                        }

                        # Check if any of the fields are empty
                        if (strlen($prerequisite_code) == 0) {
                            $row_errors[] = "blank prerequisite";
                            $check = False;
                        }
                        # Checks if any field is blank, skip the remaining validation
                        if ($check) {
                            # Check if course code referenced within this line exists within the course database 
                            if($courseDAO->retrieveByCourse($course_code) == null) {
                                # If an empty string is returned, it means that the course code referenced does not exist in the course table
                                $row_errors[] = "invalid course";
                                $check = False;
                            }

                            # Check if course code referenced within this line exists within the course database 
                            if($courseDAO->retrieveByCourse($prerequisite_code) == null) {
                                # If an empty array is returned, it means that the course code referenced does not exist in the course table
                                $row_errors[] = "invalid prerequisite";
                                $check = False;
                            }

                            # If there are no errors during validation, insert record into the database
                            if ($check) {

                                # Remove any invalid or hidden characters -- FOR MAC
                                $course_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $course_code);
                                $prerequisite_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $prerequisite_code);

                                $prerequisiteDAO->add($course_code, $prerequisite_code);
                                $prerequisite_validated += 1;
                            }
                        }

                        # Insert error msgs 
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        } 

                        # Increment the row counter 
                        $row += 1;
                    }


                    # --------------------- #
                    #    COURSE_COMPLETED   #
                    # --------------------- #
                    # Start from the course_completed.csv file 
                    $data_course = fgetcsv($course_completed); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines (insert the said lines until they fulfil all the validations)
                    $row  = 2; 
                    $filename = "course_completed.csv";
                    while (($data_course = fgetcsv($course_completed)) != FALSE) {
                        /*
                            Course_completed format: 
                                $data_course[0] - userid
                                $data_course[1] - course
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields 
                        $userid = trim($data_course[0]);
                        $course_code = trim($data_course[1]);

                        # Check if any of the fields are empty
                        $check = True;
                        if (strlen($userid) == 0) {
                            $row_errors[] = "blank userid";
                            $check = False;
                        }

                        if (strlen($course_code) == 0) {
                            $row_errors[] = "blank code";
                            $check = False;
                        }
                        #checks if any field is blank, skip the remaining validation
                        if ($check){
                                # Check if the userid exists within the database 
                            if ($studentDAO->retrieveByUserID($userid) == null){
                                $row_errors[] = "invalid userid";
                                $check = False;
                            }

                            # Check if course code referenced within this line exists within the course database 
                            if($courseDAO->retrieveByCourse($course_code) == null) {
                                # If an empty array is returned, it means that the course code referenced does not exist in the course table
                                $row_errors[] = "invalid course";
                                $check = False;
                            }

                            # Check if prerequisites for the course is met
                            # Check if the course has prerequisites
                            if ($prerequisiteDAO->retrieve($course_code) != []) {
                                # Get all the prerequisites
                                $prerequisites_required = $prerequisiteDAO->retrieve($course_code);
                                # For every prerequisite object...
                                foreach ($prerequisites_required as $a_prerequisite_required) {
                                    # Get the prerequisite courde code 
                                    $prerequisite_to_check = $a_prerequisite_required->getPrerequisite();
                                    # Check if the student has completed it 
                                    if ($courseCompletedDAO->getStudentComplete($userid, $prerequisite_to_check) == null) {
                                        $row_errors[] = "invalid course completed";
                                        $check = False;
                                    }
                                }

                            }

                            # If there are no errors during validation, insert record into the database
                            if ($check) {
                                # Remove any invalid or hidden characters -- FOR MAC --
                                $userid = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $userid);
                                $course_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $course_code);
                                $courseCompletedDAO->add($userid, $course_code);
                                $course_completed_validated += 1;
                            }
                        }

                        # Insert error msgs 
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        } 

                        # Increment the row counter 
                        $row += 1;
                    }

                    # -------- #
                    #    BID   #
                    # -------- #
                    # Start from the bid.csv file 
                    $data_course = fgetcsv($bid); # This reads the first line (header) and skips it
                    # Process the lines other than the header and validate the lines (insert the said lines until they fulfil all the validations)
                    $row  = 2; 
                    $filename = "bid.csv";
                    while (($data_course = fgetcsv($bid)) != FALSE) {
                        /*
                            Bid format: 
                                $data_course[0] - userid
                                $data_course[1] - amount 
                                $data_course[2] - code 
                                $data_course[3] - section 
                        */

                        # Row errors 
                        $row_errors = array();

                        # Initialize fields 
                        $userid = trim($data_course[0]);
                        $amount = trim($data_course[1]);
                        $course_code = trim($data_course[2]);
                        $section_no = trim($data_course[3]);

                        # Check if any of the fields are empty
                        $check = True;
                        if (strlen($userid) == 0) {
                            $row_errors[] = "blank userid";
                            $check = False;
                        }

                        if (strlen($amount) == 0) {
                            $row_errors[] = "blank amount";
                            $check = False;
                        }

                        if (strlen($course_code) == 0) {
                            $row_errors[] = "blank code";
                            $check = False;
                        }

                        if (strlen($section_no) == 0) {
                            $row_errors[] = "blank section";
                            $check = False;
                        }
                        #checks if any field is blank, skip the remaining validation
                        if($check) {
                            # Check if the userid exists within the database 
                            if ($studentDAO->retrieveByUserID($userid) == null){
                                $row_errors[] = "invalid userid";
                                $check = False;
                            }

                            # Check if amount field is in the right format (positive float that is above $10.00 with not more than 2 decimals)
                            $amount_array = explode(".", $amount);
                            if (count($amount_array) > 2) {
                                $row_errors[] = "invalid  amount";
                                $check = False;
                            } else {
                                # If the amount field is a float 
                                if (count($amount_array) == 2) {
                                    # Check if the decimal place is a numeric value or if there are more than 2 decimal places
                                    if (!is_numeric($amount_array[1]) || strlen($amount_array[1]) > 2) {
                                        $row_errors[] = "invalid amount";
                                        $check = False;
                                    } 
                                } 

                                # Check if the number field is a numeric value
                                if (!is_numeric($amount_array[0])) {
                                    $row_errors[] = "invalid amount";
                                    $check = False;
                                }

                                # Check if amount is less than 10$
                                if($amount_array[0] < 10) {
                                    $row_errors[] = "invalid amount";
                                    $check = False;
                                }
                            }

                            # Check if course code referenced within this line exists within the course database 
                            if($courseDAO->retrieveByCourse($course_code) == []) {
                                # If an empty array is returned, it means that the course code referenced does not exist in the course table
                                $row_errors[] = "invalid course";
                                $check = False;
                            } else {
                                # If the course exists within the database
                                # Check if section number referenced within this line exists within the section database 
                                $section_check = $sectionDAO->retrieveOnlySectionByCourse($course_code);
                                if(!in_array($section_no, $section_check)) {
                                    # If an empty array is returned, it means that the course code referenced does not exist in the course table
                                    $row_errors[] = "invalid section";
                                    $check = False;
                                }
                            }

                            # By default, every course loaded from the bid.csv file is considered a pre-assigned and hence is given a successful status
                            $status = "P";

                            # Perform checking for logic validations
                            if ($check) {
                                # Checks if the course and the student is from the same school
                                $user_object = $studentDAO->retrieveByUserID($userid);
                                $user_school = $user_object->getSchool();
                                $course_object = $courseDAO->retrieveByCourse($course_code);
                                $course_school = $course_object->getSchool();
                                if ($course_school != $user_school) {
                                    $row_errors[] = 'not own school course';
                                    $check = False;
                                }

                                if ($bidDAO->retrieveBidByUserIDAndCourse($userid, $course_code) != null) {
                                    $bidObj = $bidDAO->retrieveBidByUserIDAndCourse($userid, $course_code);

                                    $student_obj = $studentDAO->retrieveByUserID($userid);
                                    $wallet_amount = $student_obj->getEdollar();
                                    # Get the original bid amount 
                                    $origBid = $bidObj->getAmount();
                                    # Add it back with the student's current wallet amount before check if he/she has enough to place the bid 
                                    $current_with_origBid = $wallet_amount + $origBid;
                                    if ($amount > $current_with_origBid) {
                                        $row_errors[] = "not enough e-dollar";
                                        $check = False;
                                    }
                                } else {
                                    # Checks if the class timetable clashes with existing bids.
                                    $user_bids = $bidDAO->retrieve($userid);
                                    $timetable_clash = False;
                                    foreach ($user_bids as $a_user_bid) {
                                        $existing_bid_course = $a_user_bid->getCourse();
                                        $existing_bid_section = $a_user_bid->getSection();
                                        $existing_bid_section_object = $sectionDAO->retrieveBySectionAndCourse($existing_bid_course, $existing_bid_section);
                                        $existing_bid_section_day = $existing_bid_section_object->getDay();
                                        $current_bid_section_object = $sectionDAO->retrieveBySectionAndCourse($course_code, $section_no);
                                        $current_bid_section_day = $current_bid_section_object->getDay();
                                        if ($current_bid_section_day == $existing_bid_section_day) {
                                            $existing_bid_section_start = implode("",explode(":",$existing_bid_section_object->getStart()));
                                            $existing_bid_section_end = implode("",explode(":",$existing_bid_section_object->getEnd()));
                                            $current_bid_section_start = implode("",explode(":",$current_bid_section_object->getStart()));
                                            $current_bid_section_end = implode("",explode(":",$current_bid_section_object->getEnd()));
                                            if (($existing_bid_section_start <= $current_bid_section_start && $current_bid_section_start < $existing_bid_section_end) || ($existing_bid_section_start < $current_bid_section_end && $current_bid_section_end <= $existing_bid_section_end)) {
                                                $timetable_clash = True;
                                            }
                                        }
                                    }
                                    if ($timetable_clash == True) {
                                        $row_errors[] = 'class timetable clash';
                                        $check = False;
                                    }

                                    # Checks if the exam timetable clashes with existing bids
                                    # Checks if the exam timetable clashes with existing bids
                                    $exam_clash = False;
                                    foreach ($user_bids as $a_user_bid) {
                                        $existing_bid_course_object = $courseDAO->retrieveByCourse($a_user_bid->getCourse());
                                        $existing_bid_exam_date = $existing_bid_course_object->getExamDate();
                                        $current_bid_course_object = $courseDAO->retrieveByCourse($course_code);
                                        $current_bid_exam_date = $current_bid_course_object->getExamDate();
                                        if ($current_bid_exam_date == $existing_bid_exam_date) {
                                            $existing_bid_exam_start = implode("",explode(":",$existing_bid_course_object->getExamStart()));
                                            $existing_bid_exam_end = implode("",explode(":",$existing_bid_course_object->getExamEnd()));
                                            $current_bid_exam_start = implode("",explode(":",$current_bid_course_object->getExamStart()));
                                            $current_bid_exam_end = implode("",explode(":",$current_bid_course_object->getExamEnd()));
                                            if (($existing_bid_exam_start <= $current_bid_exam_start && $current_bid_exam_start < $existing_bid_exam_end) || ($existing_bid_exam_start < $current_bid_exam_end && $current_bid_exam_end <= $existing_bid_exam_end)) {
                                                $exam_clash = True;
                                            }
                                        }
                                    }
                                    if ($exam_clash == True) {
                                        $row_errors[] = 'exam timetable clash';
                                        $check = False;
                                    }

                                    # Checks if student has not completed the prerequisites of the course 
                                    if ($prerequisiteDAO->retrieve($course_code) != []) {
                                        $prerequisites_required = $prerequisiteDAO->retrieve($course_code);
                                        foreach ($prerequisites_required as $a_prerequisite_required) {
                                            $prerequisite_to_check = $a_prerequisite_required->getPrerequisite();
                                            if ($courseCompletedDAO->getStudentComplete($userid, $prerequisite_to_check) == null) {
                                                $row_errors[] = "incomplete prerequisites";
                                                $check = False;
                                            }
                                        }
        
                                    }
                                    # Checks if student already completed the course
                                    if ($courseCompletedDAO->getStudentComplete($userid, $course_code) != null) {
                                        $row_errors[] = 'course completed';
                                        $check = False;
                                    }

                                    # Checks if section limit reached
                                    if (count($user_bids) == 5) {
                                        $row_errors[] = "section limit reached";
                                        $check = False;
                                    }

                                    # Checks if not enough e-dollar to place bid
                                    $student_balance = $user_object->getEdollar();
                                    if ($amount > $student_balance) {
                                        $row_errors[] = "not enough e-dollar";
                                        $check = False;
                                    }
                                }

                            }
                            

                            # If there are no errors during validation, insert record into the database
                            
                            if ($check) {
                                # Convert amount to a float for insertion into the database
                                $amount = (float) $amount;

                                # Remove any invalid or hidden characters -- FOR MAC
                                $userid = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $userid);
                                $amount = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $amount);
                                $course_code = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $course_code);
                                $section_no = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $section_no);

                                # Deduct the amount 
                                $round = 1;
                                $student_obj = $studentDAO->retrieveByUserID($userid);
                                $wallet_amount = $student_obj->getEdollar();
                                if (!isset($bidObj)) {
                                    # Deduct the amount 
                                    $new_amount = $wallet_amount - $amount;
                                    $studentDAO->updateBalance($userid, $new_amount);
                                    $bidDAO->add($userid, $amount, $course_code, $section_no, $status, $round);
                                    
                                } else {
                                    $origBid = $bidObj->getAmount();
                                    $new_amount = ($wallet_amount + $origBid) - $amount;
                                    $bidDAO->removeBid($userid, $course_code);
                                    $bidDAO->add($userid, $amount, $course_code, $section_no, $status, $round);
                                    $studentDAO->updateBalance($userid, $new_amount);
                                }
                                $bid_validated += 1;

                            }
                        }

                        # Insert error msgs 
                        if (count($row_errors) <> 0) {
                            $errors[] = ["file"=>$filename, "line" => $row, "message" => $row_errors];
                        }   
                        

                        # Increment the row counter 
                        $row += 1;

                    }

                //     # ================================== VALIDATION END ========================================

                }

                if (count($errors) == 0) {
                    $result = [ 
                        "status" => "success",
                        "num-record-loaded" => [
                            ["bid.csv" => $bid_validated ],
                            ["course.csv" => $course_validated],
                            ["course_completed.csv" => $course_completed_validated],
                            ["prerequisite.csv" => $prerequisite_validated],
                            ["section.csv" => $section_validated],
                            ["student.csv" => $student_validated]
                        ]
                    ];
                }
                
                if (count($errors) != 0) {
                    $errorFilename = [];
                    foreach ($errors as $anError) {
                        $errorFilename[] = $anError['file'];
                    }
                    array_multisort($errorFilename, SORT_ASC,$errors);
                    $result = [ 
                        "status" => "error",
                        "num-record-loaded" => [
                            ["bid.csv" => $bid_validated ],
                            ["course.csv" => $course_validated],
                            ["course_completed.csv" => $course_completed_validated],
                            ["prerequisite.csv" => $prerequisite_validated],
                            ["section.csv" => $section_validated],
                            ["student.csv" => $student_validated]
                        ],
                        "error" => $errors
                    ];

                }


            }
        }
        
    $settingsDAO = new SettingsDAO();
    $settingsDAO->setRoundNumber(1);
    $settingsDAO->setBiddingAllowed(1);
    return $result;
}
?>