<?php 
/* 
Displays a message informing user that the new referendum was created successfully; 
receives referendum ID via GET and updates the status of the referendum to "not started"
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


include('session.php');
include('connection_db.php');
include('sanitise.php');
?>


<!DOCTYPE html>
<html>
<head>
<title>HandiVote</title>

<?php
include('script.js');
include('css_files.php');
?>



<style type="text/css">     

body
{
  background-color: Snow;
  margin-top:10px;
  margin-left:100px;
}

fieldset
{
border:1px solid white;
margin-top: 50px;
margin-bottom: 5px;
font-size: 120%;
background-color: Beige;
padding: 10px;
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

/* if no referendum ID supplied, terminate */
if (empty($_GET['id'])){
  die();
}

$id= sanitize($_GET['id']);

/* get the referendum */
$get_ref = sprintf("SELECT * FROM `referendum` WHERE `id` like '%s';", $id);
$result = mysql_query($get_ref, $con);
if ($row = mysql_fetch_assoc($result)){

	/* if the status is different from "draft" - terminate */
	if ($row['status']!='draft'){
		die();
	}
	else{
		/* update the status to "not started" */
        $update_status = sprintf("UPDATE `referendum` SET `status` = 'not started' WHERE `id`= '%s';", $id);


     $result_update = mysql_query($update_status, $con);
     if (!$result_update) {die('Could not connect: ' . mysql_error());}

print "<fieldset>";

/* success message */
print "<h4>Thank you. The referendum was successfully created.</h4></br></br>";

/* button back to main admin page */
print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";

print "</fieldset>";
	}
}

 /* if they got here, this means that the referendum id could not be found in the database; terminate */
else{
	die();
}





//close the connection to the database

mysql_close($con);
?>