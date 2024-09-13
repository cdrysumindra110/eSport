<?php
session_start(); // Start the session

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the login page or home page
header("Location: signin.php");
exit(); // Ensure no further code is executed
?>
