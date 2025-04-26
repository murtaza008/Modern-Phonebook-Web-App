<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page with correct path
header("Location: ../login/login.php");
exit();
?> 