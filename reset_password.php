<?php

if (count(get_included_files()) == 1)
    exit("Access restricted.");

/* Password reset process, updates database with new user password */
require 'scripts/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Make sure the form is being submitted with method="post"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newpass = $_POST['newpassword'];
    // Checks that the password is long enough
    if (!strlen($newpass) < 8) {
        // Make sure the two passwords match
        if ($newpass == $_POST['confirmpassword']) {

            $new_password = password_hash($newpass, PASSWORD_BCRYPT);

            // We get $_POST['email'] and $_POST['hash'] from the hidden input field of reset.php form
            $email = $mysqli->escape_string($_POST['email']);
            $hash = $mysqli->escape_string($_POST['hash']);

            $sql = "UPDATE users SET password='$new_password', hash='$hash' WHERE email='$email'";

            if ($mysqli->query($sql)) {

                $_SESSION['message'] = "Your password has been reset successfully!";
                header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/" . "success.php");
            }
        } else {
            $_SESSION['message'] = "Two passwords you entered don't match, try again!";
            header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/" . "error.php");
        }
    } else {
        $_SESSION['message'] = "The new password is too short! Please try again. The password must be at least 8 characters long.";
        header("Location: http://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/" . "error.php");
    }
}
?>