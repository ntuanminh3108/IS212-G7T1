<?php
// import required files
require_once 'include/common.php';
?>

<!-- Import Bootstrap 4 Framework for UI modifications -->
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
        width: 29% !important; 
        }
    </style>
    <!-- UI for header in dropsection form -->
    <div class = 'row'>
        <div class = 'col-sm-6'>
            <div class="container-fluid"><img src="resources/images/logo.jpg" height="1440" width="600" class="img-fluid"></div>
        </div>
    </div>
    </head>
</html>

<html>
<!-- Page Heading -->
<div class='alert alert-primary' role='alert'><div class='container text-center'><h1>Drop an enrolled course</h1></div></div>
<?php
// If there are any errors when dropping a section, print the appropriate error messages.
if (isset($_SESSION['errors'])) {
                echo"
                    <div class = 'container text-center'>
                    <div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
                    <div class='alert1'>
                ";
                printMessage();
                printErrors();
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

<!-- Create table for displaying enrolled courses -->
<div class = 'container text-center'>
<h3><p><strong>Enrolled course(s) </strong></p></h3>
<table class = 'table' border='1'>
    <thead class='thead-dark'>
<tr><th>Course</th><th>Section</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
</thead>
<?php

// Find all bids made by the user
$userid = $_SESSION['userID'];
$bidDAO = new BidDAO();
$userbids = $bidDAO->retrieve($userid);

if ($userbids) {
    $enrolled_courses = [];

    $settingsDAO = new SettingsDAO();
    $bidding_allowed = $settingsDAO->getBiddingAllowed();
    $round_number = $settingsDAO->getRoundNumber();

    // get all bids made by the user that are successful.
    if ($bidding_allowed == 0) {
        if ($round_number == 1) {
            foreach ($userbids as $a_bid) {
                if ($a_bid->getStatus() == "S" && $a_bid->getRound() == 1) {
                    $enrolled_courses[] = $a_bid;
                }
            }
        } else {
            foreach ($userbids as $a_bid) {
                if ($a_bid->getStatus() == "S") {
                    $enrolled_courses[] = $a_bid;
                }
            }
        }
    } else {
        foreach ($userbids as $a_bid) {
            if ($a_bid->getStatus() == "S" && $a_bid->getRound() == 1) {
                $enrolled_courses[] = $a_bid;
            }
        }
    }
    // if user has no enrolled courses, return the appropriate message.
    if(count($enrolled_courses) == 0) {
        echo "<tr><td colspan='5'> You are currently not enrolled into any courses.</td>";
    } else {

        $status_message = array("S"=>"Success", "P"=>"Pending",   
                  "F"=>"Failed"); 
        // return all successful bid made by the user and add a button which will drop that particular section if the user clicks it.
        echo "<form action='dropSection.php' method='post'>";
        foreach($enrolled_courses as $an_enrolled_course) {
            $course = $an_enrolled_course->getCourse();
            $section = $an_enrolled_course->getSection();
            $amount = $an_enrolled_course->getAmount();
            $status = $an_enrolled_course->getStatus();

            $button_value = $course . "-" . $section;
            
            echo "<tr>";
            echo "<td>$course</td><td>$section</td><td>$amount</td><td>$status_message[$status]</td>";
            echo "<td><button name='section_to_drop' type='submit' class = 'btn btn-primary' value='$button_value'>Drop Section</button></td>";
            echo "</tr>";    
        }
        echo "</form>";
    }
    
} else {
    echo "<tr><td colspan='5'> You are currently not enrolled into any courses.</td>";
}




?>
</table>
<br/>
</div>
<!--  Return to homepage button  -->
<div class = 'container text-center'>
<form action='student_homepage.php'><input type='submit' button type='button' class='btn btn-primary' name='return' value='Return to Homepage'></form>
</div>