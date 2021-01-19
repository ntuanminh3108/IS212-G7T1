<!DOCTYPE html>
<html lang="en">
<head>
  <title>BIOS - Login</title>
  <!-- Import Bootstrap 4 Framework for Web UI Modifications -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>
<!-- Logo for Login page -->
<div class="container-fluid">
  <img src="resources/images/logo.jpg" class="img-fluid">
</div>

<!-- Login Page - welcome message and login form -->
<div class="container">
  <div class='row'>
    <div class="col-sm-6">
    <h1 class="display-4 text-center">Welcome to BIOS!</h1>
    <p class="text-center">Use your userID and password to log in.</p>
    </div>
  

  
    <div class="col-sm-6">
      <form action="authenticate.php" method='post' class="was-validated">
        <div class="form-group">
          <label for="userID">Username:</label>
          <input type="text" class="form-control" id="userID" placeholder="Enter username" name="userID" required>
          <div class="invalid-feedback">This is a required field.</div>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" class="form-control" id="password" placeholder="Enter password" name="password" required>
          <div class="invalid-feedback">This is a required field.</div>
        </div>
        <button type="submit" class="btn btn-primary">Log In</button>
        </form>
      </div>
  </div>
</div>
<br/>
<?php
// Print appropriate error messages if login was unsuccessful.
require_once 'include/common.php';
if (isset($_SESSION['errors'])) {
  echo "<div class = 'row'>
        <div class='col-sm-6'>
        </div>
        <div class='col-sm-6'>
        <div class='alert  alert-warning alert-dismissible fade show' role='alert' style='display:inline-block;'>
        <div class='alert1'>";
  printErrors();
  echo "</div>";
		echo '
			<div class="alert1">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
   		    <span aria-hidden="true">&times;</span>
  			</button>
        </div>
        </div>
		';
}

//Unset $_SESSION['userid'] after being redirected back to login page after logging out.
if (isset($_SESSION['userID'])) {
  unset($_SESSION['userID']);
}
?>
</body>
</html> 