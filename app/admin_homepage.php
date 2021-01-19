<?php 
// import required files
  require_once 'include/common.php';
  require_once 'include/protect.php';
?>

<!-- Bootstrap 4 Framework for UI modifications -->
<head>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<title>BIOS - Admin Homepage</title>
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
  </style>

  <!-- Header and Logout Button for Admin Homepage based on Bootstrap 4 Framework -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  <nav class="navbar navbar-light navbar-expand-md bg-faded justify-content-center">
        <img src="resources/images/logo.jpg" height="15%" width="15%" class="navbar-brand d-flex mr-auto" />
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar3">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse w-100" id="collapsingNavbar3">
            <ul class="nav navbar-nav ml-auto w-100 justify-content-end">
                <li class="nav-item">
                    <p class="nav-link active">Welcome, Admin</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>


</head>
<?php

// get the round number and whether bidding is allowed from the database.
$settingsDAO = new SettingsDAO();
$roundnumber = $settingsDAO->getRoundNumber();
$biddingAllowed = $settingsDAO->getBiddingAllowed();
echo "
</br> 
<div class='alert alert-primary' role='alert'>
		<div class='container text-center'>
        <h2>Round number: $roundnumber</h2>";
		
// convert the bidding allowed(in the form of 0 or 1) to the corresponding readable format.
if ($biddingAllowed == TRUE) {
	echo "<p>Bidding is currently allowed</p></div></div>";
}
else {
	echo "<p>Bidding is currently not allowed</p></div></div>";
}
echo "</br></br>";

?>
<html>
<!-- Form and Button for Bootstrapping -->
<form id='bootstrap-form' action="bootstrap_process.php" method="post" enctype="multipart/form-data">

<div class = 'container text-center'>
<button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#bootstrapModal">
			<div class = 'btn-xl'>
  			Bootstrap
			</div>
</button>
</div>

<div class="modal fade" id="bootstrapModal" tabindex="-1" role="dialog" aria-labelledby="bootstrapModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="bootstrapModalLabel">Bootstrap</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	  Bootstrap file: 
 		 <input id='bootstrap-file' type="file" name="bootstrap-file"><br/>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <input type="submit" name="submit" button type="button" class="btn btn-primary" value="Import">
      </div>
    </div>
  </div>
</div>
</div>
</form>

<br/>
<!-- Form and Buttons for Starting and Stopping Round -->
<div class = 'container text-center'>
<form action='include/round_start_end.php' method='GET'>
	<div class="btn-group" role="group" aria-label="round_related">
	  <input type = 'submit' name='round_action' button type="button" class="btn btn-secondary" value='End Round'>
	  <input type = 'submit' name='round_action' button type="button" class="btn btn-secondary" value='Start Round'>
	</div>
</form>
</div>


<br/>
<br/>
</html>



<?php
// Messages for bootstrap status, number of records loaded, bootstrap errors, or round start/stop.
	if (isset($_SESSION['bootstrap-message'])) {
		echo"
			<div class = 'container text-center'>
			<div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
			<div class='alert1'>
		";
		echo $_SESSION['bootstrap-message'];
		echo "</div>";
		echo '
			<div class="alert1">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
   		    <span aria-hidden="true">&times;</span>
  			</button>
  			</div>
		';
		echo "</div></div>";
		echo '<br/><br/>';
		
		unset($_SESSION['bootstrap-message']);
		
	}

	if (isset($_SESSION['num-record-loaded'])) {
		echo "	<div class = 'container text-center'><h3>Number of records loaded:</h3></div><br/>
				<div class = 'container text-center'>
				<table class = 'table' border='1'>
				<thead class='thead-dark'>
				<tr><th>File name</th><th>Number of records loaded</th>
				</thead>
			";
		foreach ($_SESSION['num-record-loaded'] as $recordLoaded) {
			foreach ($recordLoaded as $filename => $recordNum) {
				echo "<tr><td>$filename</td><td>$recordNum</td></tr>";
			}
		}
		echo "</table></div>";
		unset($_SESSION['num-record-loaded']);
	}

	if (isset($_SESSION['bootstrap-errors'])) {
		echo"<div class = 'container text-center'>";
		echo "
				</br><h3> Errors in bootstrap files: </h3></br>
				<table class = 'table' border='1'>
    			<thead class='thead-dark'>
    			<tr><th>File name</th><th>Line</th><th>Message</th>
				</thead>
			";
		foreach ($_SESSION['bootstrap-errors'] as $anError) {
			$file = $anError["file"];
			$line = $anError["line"];
			$message = implode(",",$anError["message"]);
			echo"
			<tr><td>$file</td><td>$line</td><td>$message</td>
			";
		}
		echo "</div>";
		
		echo "</table>";
		unset($_SESSION['bootstrap-errors']);
	}
	if (isset($_SESSION['round-message'])) {
		echo"
			<div class = 'container text-center'>
			<div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
			<div class='alert1'>
		";
		echo $_SESSION['round-message'];
		echo "</div>";
		echo '
			<div class="alert1">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
   		    <span aria-hidden="true">&times;</span>
  			</button>
  			</div>
		';
		echo "</div></div>";
		echo '<br/><br/>';
		unset($_SESSION['round-message']);
	}
?>