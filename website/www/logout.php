<?PHP

/* 
Logout page, destroys the session variables
Author: Tsvetelina Valcheva
Last Modified: 29/01/13
*/


/* destroy the session (need to start it first in order to be able to destroy it) */
session_start();
session_destroy();


?>


<!DOCTYPE html>
<html>
<head>
<title>HandiVote</title>
<?php
include('css_files.php');

/* the following style settings are adjusted for this page */
?>

<style type="text/css">     

body
{
  background-color: Snow;
  margin-top:20px;
  margin-left:30px;
}
div
{
margin-top: 5px;
margin-bottom: 5px;
}
</style>
</head>
<body>
<?php

/* title */
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";

/* success message */
print "<h4>You have successfully logged out. </h4><br/>";

/* Button to login.php page - in case the user wants to login again*/
print"<form method='link' action='login.php'>
<input type='submit' class='btn btn-success' value='Click Here to Login Again'>
</form>";
?>
</body>
</html>