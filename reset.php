<?php
/* Form for resetting password */

require 'db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Checking that the email and hash variables are set
// Get carries the variables passed to the script via the URL parameters
if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){
    $email 	= $mysqli->escape_string($_GET['email']); 
    $hash 	= $mysqli->escape_string($_GET['hash']); 

    // Checks if a user with the matching hash exists in the database
    $result = $mysqli->query("SELECT * FROM users WHERE email='$email' AND hash='$hash'");
	
	// If the query results in zero rows, the user does not exist.
    if ($result->num_rows == 0){ 
        $_SESSION['message'] = "You have entered invalid URL for password reset!";
        header("location: error.php");
    }
}
else {
    $_SESSION['message'] = "The reset link is not valid, try again!";
    header("location: error.php");  
}
?>

<!DOCTYPE html>
<html >
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Password Reset</title>
  	<link href="css/upstrain.css" rel="stylesheet">
</head>

<body>
	<?php include 'top.php'; ?>
	<main>
		<div class="form">
			<h1>Choose a new password</h1>
			<form action="reset_password.php" method="post">
				<div class="field-wrap">
					<label>
						New Password<span class="req">*</span>
					</label>
					<input class="login" pattern=".{8,}" type="password"required name="newpassword" autocomplete="off" required title="The password must be at least 8 characters"/>
				</div>
				  
				<div class="field-wrap">
					<label>
						Confirm New Password<span class="req">*</span>
					</label>
					<input class="login" pattern=".{8,}" type="password"required name="confirmpassword" autocomplete="off" required title="The password must be at least 8 characters"/>
				</div>
			  
				<!-- This input field is needed, to get the email of the user -->
				<input  class="login" type="hidden" name="email" value="<?= $email ?>">    
				<input  class="login" type="hidden" name="hash" value="<?= $hash ?>">    
				  
				<button class="button"/>Reset</button>
			</form>
		</div>
		<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
		<script src="js/index.js"></script>
	</main>
	<?php include 'bottom.php'; ?>
</body>
</html>
