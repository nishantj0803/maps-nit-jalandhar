<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
// Path is correct as index.html is in the same directory
header("Location: index.html?logout=success"); // Added query param for feedback
exit();
?>
