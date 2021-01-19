<?php
// import required files.
require_once 'include/common.php';

// obtain the userid of the student who logged in, as well as his/her name & e-dollar balance.
$userID = $_SESSION['userID'];
$studentDAO = new StudentDAO();
$student = $studentDAO->retrieveByUserID($userID);
$name = $student->getName();
$eDollarBalance = $student->getEdollar();

?>
<!-- Import Bootstrap 4 Framework. The following code is the corresponding UI modifications -->
<html>
    <head>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <meta charset="utf-8">
    <style type="text/css">
        .line{
            width: 1450px;
            height: 47px;
            border-bottom: 1px solid black;
            position: absolute;
        }
        .btn-xl {
            width: 500px;
            padding: 10px 20px;
            font-size: 30px;
            border-radius: 10px;
        }
        .alert1 {
            display:inline-block;
        }
        .table th {
            text-align: center;
        }

        .table {
        margin: auto;
        width: 40% !important; 
        }
    </style>
    <nav class="navbar navbar-light navbar-expand-md bg-faded justify-content-center">
        <img src="resources/images/logo.jpg" height="15%" width="15%" class="navbar-brand d-flex mr-auto" />
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar3">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse w-100" id="collapsingNavbar3">
            <ul class="nav navbar-nav ml-auto w-100 justify-content-end">
                <li class="nav-item">
                    <p class="nav-link active">Welcome, <?= $name ?></a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    </head>
</html>

<html>
<body>
    <div class='alert alert-primary' role='alert'><div class='container text-center'><h1>Bid for a Course</h1></div></div>
    <?php
        
    // Obtain the bidding round and whether bidding is allowed.
    $settingsDAO = new SettingsDAO();
    $roundnumber = $settingsDAO->getRoundNumber();
    $biddingAllowed = $settingsDAO->getBiddingAllowed();

    // echo the following information for the user - edollar balance, round number and whether bidding is allowed.
    echo " 
    
            <div class='container text-center'>
            <h4>Your e-dollar balance:$ {$eDollarBalance}</h4>
            <h4>Round number: $roundnumber</h4>";
            
    if ($biddingAllowed == TRUE) {
        echo "<p>Bidding is currently allowed</p></div>";
    }
    else {
        echo "<p>Bidding is currently not allowed</p></div>";
    }

    ?>
     
    <?php
    // generates appropriate error messages should bidding be unsuccessful.
        if (isset($_SESSION['errors'])) {
            echo"
                <div class = 'container text-center'>
                <div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
                <div class='alert1'>
            ";
            printMessage();
            echo " Reason: ";
            $print_err = "";
            foreach ($_SESSION['errors'] as $err) {
              $print_err .= $err . ", ";
            }
            echo substr($print_err, 0, strlen($print_err)-2);
            unset($_SESSION['errors']);
            echo '
                <div class="alert1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                </div>
            ';
            echo "</div></div>";
        } elseif (isset($_SESSION['message'])) {
            echo"
                <div class = 'container text-center'>
                <div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
                <div class='alert1'>
            ";
            printMessage();
            echo '
                <div class="alert1">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                </div>
            ';
            echo "</div></div>";
        }

    ?>

    <!-- The following is the form for adding bids. The information keyed into this 
        form will be transferred to addBid.php for processing. -->
    <div class = 'container text-center'>
    <div class='table'>
    <form action='addBid.php' method='POST'>
        <table>
            <tr>
                <td><b>Course:</b></td>
                <td><input type='text' name='course'><td>
            </tr>
            <tr>
                <td><b>Section:</b></td>
                <td><input type='text' name='section'></td>
            </tr>
            <tr>
                <td><b>Bid Amount:</b></td>
                <td><input type ='number' step ='0.01' name = 'bid_amount'></td>
                <td></td>
            </tr>
            <tr>
        </table>
        <input type='submit' name='bid_action' button type="button" class="btn btn-primary" value='Submit Bid'>
    </form>
    </div>

    <?php
    // This is to allow the user to return back to homepage.
    echo "<form action='student_homepage.php'><input type='submit' name='return' button type='button' class='btn btn-primary' value='Return to Homepage'></form>";
    echo "</br>";
    ?>
    </div>
    </div>

    <!-- The following is the additional functionality for students to search for a particular course or section -->
    <div class='alert alert-primary' role='alert'><div class='container text-center'><h2> Course Information </h2></div></div>
    
    <!-- This is the form to enter the filter inputs. Users can filter by a particular course and/or section -->
    <form action='add_bid_form.php' method='GET'>
        <div class= 'container text-center'>
        <b>Course:</b>
        <input type = 'text' name = 'CourseNameToSearch' placeholder = 'Search for course'>
        <b>Section:</b>
        <input type = 'text' name = 'SectionNameToSearch' placeholder = 'Search for section'>
        </br></br>
        <input type='submit' button type="button" class="btn btn-primary" name='CourseSearch' value='Filter'>
        <button class="btn btn-primary" onClick="window.location.href=window.location.href">Reset</button>
        </div>
    </form>

<?php
// The following is the code and functions required for searching and filtering courses and/or sections. 
if (isset($_GET['CourseSearch']) || !empty($_GET['CourseSearch'])){
    if(isset($_GET['CourseNameToSearch']) and isset($_GET['SectionNameToSearch'])){
        $CourseNameToSearch = $_GET['CourseNameToSearch'];
        $SectionNameToSearch = $_GET['SectionNameToSearch'];
        $query = "SELECT * FROM `section` WHERE CONCAT(`course`) LIKE '%".$CourseNameToSearch."%' AND CONCAT(`section`) LIKE '%".$SectionNameToSearch."%'";
        $search_result = filterTable($query);

    }
    elseif(isset($_GET['CourseNameToSearch']) and !isset($_GET['SectionNameToSearch'])){
        $CourseNameToSearch = $_GET['CourseNameToSearch'];
        $query = "SELECT * FROM `section` WHERE CONCAT(`course`) LIKE '%".$CourseNameToSearch."%'";
        $search_result = filterTable($query);
        
    }
    elseif(isset($_GET['SectionNameToSearch']) and !isset($_GET['CourseNameToSearch'])){
        $SectionNameToSearch = $_GET['SectionNameToSearch'];
        $query = "SELECT * FROM `section` WHERE  LIKE CONCAT(`section`) '%".$SectionNameToSearch."%'";
        $search_result = filterTable($query);
        
    }

    $sectionDAO = new SectionDAO();
    $settingsDAO = new SettingsDAO();

    $bidDAO = new BidDAO();
    $sections = $sectionDAO->getAllSections();
echo "

<table class='table' border = '1'>

";
while($row = mysqli_fetch_array($search_result)):

    $courseDAO = new CourseDAO();
    $course = $courseDAO-> retrieveByCourse($row['course']);
    $title = $course->getTitle();
    $description = $course->getDescription();
    $totalAvailableSeats = $row['size'] - count($bidDAO->retrieveBidBySectionForRound($row['course'],$row['section'],'S',1));
    if ($settingsDAO->getRoundNumber() == 2 && $settingsDAO->getBiddingAllowed() == 0) {
        $totalAvailableSeats -= count($bidDAO->retrieveBidBySectionForRound($row['course'],$row['section'],'S',2));
    }
    $enrolled = $row['size'] - $totalAvailableSeats;
    $examdate = $course->getExamDate();
    $examstart = $course->getExamStart();
    $examend= $course->getExamEnd();
    $examyear = substr($examdate,0,4);
    $exammonth = substr($examdate,4,2);
    $examday = substr($examdate,6,2);
    $day = '';
    if ($row['day'] == 1){
        $day = 'Monday';
    }
    elseif($row['day'] == 2){
        $day = 'Tuesday';
    }
    elseif($row['day'] == 3){
        $day = 'Wednesday';
    }
    elseif($row['day'] == 4){
        $day = 'Thursday';
    }
    elseif($row['day'] == 5){
        $day = 'Friday';
    }

    
echo "
    <thead class = 'thead-dark'>
    <th colspan = '50'>"; echo $row['course'].'-'.$title;
        echo '</th></thead>';
        echo'<tr>';
        echo "<td>";
        echo '<b>Description: </b>'.' '.$description;
        echo '</br></br>';
        echo '<b>Section: </b>'.' '.$row['section'];
        echo '</br></br>';
        echo '<b>Total: </b>'.$row['size'];
        echo "</br></br>";
        echo "\t\t";
        echo '<b>Enrolled: </b>'.$enrolled;
        echo "</br></br>";
        echo "\t\t";
        echo '<b>Vacancies: </b>'.$totalAvailableSeats;
        echo '</br></br>';
        echo '<b>Min Bid Amount: </b>'.$row['minBid'];
        echo '</br></br>';
        echo '<table class = "table">';
        echo '

            <tr>
                <th>Type</th>
                <th>Date</th>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Room</th>
                <th>Instructor</th>
            </tr>
        ';
        echo "
            <tr>
            <td>Class</td>
            <td></td>
            <td>{$day}</td>
            <td>{$row['start']}</td>
            <td>{$row['end']}</td>
            <td>{$row['venue']}</td>
            <td>{$row['instructor']}</td>
            </tr>
        ";
        echo "
            <tr>
            <td>Exam</td>
            <td>{$examday}/{$exammonth}/{$examyear}</td>
            <td></td>
            <td>{$examstart}</td>
            <td>{$examend}</td>
            <td></td>
            <td></td>
            </tr>
        ";
        echo"</table>";
        echo "</td>";
        echo '</tr>';


            
endwhile;
echo "</table>";



}

else{
$courseDAO = new CourseDAO();
$sectionDAO = new SectionDAO();
$settingsDAO = new SettingsDAO();
$bidDAO = new BidDAO();
$sections = $sectionDAO->getAllSections();
echo "

<table class='table' border = '1'>

";
foreach ($sections as $aSection) {

    $course = $aSection->getCourse();
    $section = $aSection->getSection();
    $day = $aSection->getDay();
    $start = $aSection->getStart();
    $end = $aSection->getEnd();
    $instructor = $aSection->getInstructor();
    $venue = $aSection->getVenue();
    $size = $aSection->getSize();
    $totalAvailableSeats = $size - count($bidDAO->retrieveBidBySectionForRound($course,$section,'S',1));
    if ($settingsDAO->getRoundNumber() == 2 && $settingsDAO->getBiddingAllowed() == 0) {
        $totalAvailableSeats -= count($bidDAO->retrieveBidBySectionForRound($course,$section,'S',2));
    }
    $minBid = $aSection->getMinBid();
    $vacancy = $totalAvailableSeats;
    $courseinfo = $courseDAO -> retrieveByCourse($course);
    $title = $courseinfo->getTitle();
    $description = $courseinfo->getDescription();
    $enrolled = $size - $totalAvailableSeats;
    $examdate = $courseinfo->getExamDate();
    $examstart = $courseinfo->getExamStart();
    $examend= $courseinfo->getExamEnd();
    $examyear = substr($examdate,0,4);
    $exammonth = substr($examdate,4,2);
    $examday = substr($examdate,6,2);
    $changed_day = '';
    if ($day == 1){
        $day = 'Monday';
    }
    elseif($day == 2){
        $day = 'Tuesday';
    }
    elseif($day == 3){
        $day = 'Wednesday';
    }
    elseif($day == 4){
        $day = 'Thursday';
    }
    elseif($day == 5){
        $day = 'Friday';
    }

    echo "
    <thead class = 'thead-dark'>
    <th colspan = '50'>"; echo $course.'-'.$title;
        echo '</th></thead>';
        echo'<tr>';
        echo "<td>";
        echo '<b>Description: </b>'.' '.$description;
        echo '</br></br>';
        echo '<b>Section: </b>'.' '.$section;
        echo '</br></br>';
        echo '<b>Total: </b>'.$size;
        echo "</br></br>";
        echo "\t\t";
        echo '<b>Enrolled: </b>'.$enrolled;
        echo "</br></br>";
        echo "\t\t";
        echo '<b>Vacancies: </b>'.$totalAvailableSeats;
        echo '</br></br>';
        echo '<b>Min Bid Amount: </b>'.$minBid;
        echo '</br></br>';
        echo '<table class = "table">';
        echo '

            <tr>
                <th>Type</th>
                <th>Date</th>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Room</th>
                <th>Instructor</th>
            </tr>
        ';
        echo "
            <tr>
            <td>Class</td>
            <td></td>
            <td>{$changed_day}</td>
            <td>{$start}</td>
            <td>{$end}</td>
            <td>{$venue}</td>
            <td>{$instructor}</td>
            </tr>
        ";
        echo "
            <tr>
            <td>Exam</td>
            <td>{$examday}/{$exammonth}/{$examyear}</td>
            <td></td>
            <td>{$examstart}</td>
            <td>{$examend}</td>
            <td></td>
            <td></td>
            </tr>
        ";
        echo"</table>";
        echo "</td>";
        echo '</tr>';

}
echo "</table>";


}


function filterTable($query)
{   
    $host = "localhost";
    $username = "root";

    if (PHP_OS === "Darwin") {
        $password = "root";
    } 
    elseif (PHP_OS === "WINNT") {
        $password = "";
    }

    $dbname = "spm_database"; 
    $port = 3306; 
    $connect = mysqli_connect($host, $username, $password, $dbname);
    $filter_Result = mysqli_query($connect, $query);
    return $filter_Result;
}

unset($_GET['CourseSearch']);
unset($_GET['CourseNameToSearch']);
unset($_GET['SectionNameToSearch']);

?>

</body>
</html>