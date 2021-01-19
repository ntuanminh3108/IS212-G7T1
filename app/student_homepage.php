<?php
    // importing required files.
    require_once 'include/common.php';
    require_once 'include/protect.php';

    // obtain information about the student - userid, name and e-dollar balance.
    $userID = $_SESSION['userID'];
    $studentDAO = new StudentDAO();
    $student = $studentDAO->retrieveByUserID($userID);
    $eDollarBalance = $student->getEdollar();
    $name = $student->getName();
?>

<html>
    <head>
    <!-- Import Bootstrap 4 Framework for Web UI modifications -->
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

    <?php
        

    $settingsDAO = new SettingsDAO();
    $roundnumber = $settingsDAO->getRoundNumber();
    $biddingAllowed = $settingsDAO->getBiddingAllowed();
    echo "
    </br> 
    <div class='alert alert-primary' role='alert'>
            <div class='container text-center'>
            <h2>Your e-dollar balance:$ {$eDollarBalance}</h2>
            <h5>Round number: $roundnumber</h5>";
            
    if ($biddingAllowed == TRUE) {
        echo "<p>Bidding is currently allowed</p></div></div>";
    }
    else {
        echo "<p>Bidding is currently not allowed</p></div></div>";
    }
    echo "</br>";

    ?>

    <?php
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

    <div class = "container text-center"><h3><strong>Bidded Sections Table</strong></h3></div>
    </br>
    <div class   = 'text-center'>
        
    <table class = 'table' border = '1'>
        <thead class = 'thead-dark'>
        <tr><th>Course</th><th>Section</th><th>Amount</th><th>Min Bid</th><th>Vacancies</th><th>Status</th><th>Bid made in round</th><th>Actions</th></tr>
        </thead>
        <?php
        $bidDAO = new BidDAO();
        $sectionDAO = new SectionDAO();

        $userbids = $bidDAO->retrieve($userid);
        if ($userbids) {
            echo "<form action='deleteBid.php' method='post'>";
            foreach ($userbids as $a_bid) {
                $course = $a_bid->getCourse();
                $section = $a_bid->getSection();
                $amount = $a_bid->getAmount();
                $status = $a_bid->getStatus();
                $minBid = $sectionDAO->retrieveBySectionAndCourse($course, $section)->getMinBid();
                $vacancies = $sectionDAO->retrieveBySectionAndCourse($course, $section)->getVacancy();
                $round = $a_bid->getRound();
                $size = $sectionDAO->retrieveBySectionAndCourse($course, $section)->getSize();
                $totalAvailableSeats = $size - count($bidDAO->retrieveBidBySectionForRound($course,$section,'S',1));
                if ($settingsDAO->getRoundNumber() == 2 && $settingsDAO->getBiddingAllowed() == 0) {
                    $totalAvailableSeats -= count($bidDAO->retrieveBidBySectionForRound($course,$section,'S',2));
                }

                $status_message = array("S"=>"Success", "P"=>"Pending",   
                  "F"=>"Failed"); 

                echo "<tr>";
                echo "<td>$course</td><td>$section</td><td>$amount</td><td>$minBid</td><td>$totalAvailableSeats</td><td>$status_message[$status]</td><td>$round</td>";
                if ($biddingAllowed == 0) {
                    echo "<td> Bidding window has closed. </td>";
                } else {
                    if ($roundnumber == 1) {
                        echo "<td><a href='deleteBid.php?course_code=$course&section_no=$section'>Drop bid</a></td>";
                    } else {
                        if($round == 1) {
                            if ($status == "S") {
                                echo nl2br("<td> Bid made in Round 1 was successful. \n \n Remove by dropping section.</td>");
                            } else {
                                echo nl2br("<td>N/A</td>");
                            }
                        } else {
                            echo "<td><a href='deleteBid.php?course_code=$course&section_no=$section'>Drop bid</a></td>";
                        }
                    }
                }  
            }
            echo "</form>";
        }
        else {
            echo "<tr><td colspan='8'> No bids found in database.</td>";
        }
        ?>
    </table>

    <?php
        if ($biddingAllowed == 1) {
            echo "</div>
                    <br/>
                    <br/>
                    <div class = 'container text-center'>
                        <div class='btn-group' role='group' aria-label='bid_related'>
                            <form action='add_bid_form.php' method='POST'> 
                                <input type = 'submit' name='bid_action' button type='button' class='btn btn-secondary' value='Make a bid'>
                            </form>
                            <form action='drop_section_form.php' method='POST'>
                                <input type = 'submit' name='bid_action' button type='button' class='btn btn-secondary' value='Drop a section'>
                            </form>
                        </div>
                    </div>
                </div>";
        }
    ?>
    
</html>