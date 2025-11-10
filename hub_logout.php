<?php
session_start();
session_destroy();

// Testing Public Repository
// Testing 1 2 3 4 5 
// // Redirect to login page after session is destroyed
header("Location: main/hub_home.php");
exit(); // Terminate script execution after redirect

?>
