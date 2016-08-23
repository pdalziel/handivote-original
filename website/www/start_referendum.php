<?php 

/* 
Start a referendum, referendum ID supplied via GET
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/

include('session.php');
include('script.js');
include('connection_db.php');
include('sanitise.php');
?>


<!DOCTYPE html>
<html>
<head>
<title>HandiVote</title>

<?php
include('css_files.php');
?>
<style type="text/css">     

body
{
  background-color: Snow;
  margin-top:10px;
  margin-left:50px;
}

fieldset
{
  width:23em;
border:1px solid white;
margin-top: 5px;
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

/*title*/

print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";


/* if no referendum ID supplied, terminate */
if (empty($_GET['id'])){
  die();
}

  $id = sanitize($_GET['id']);


/* get the referendum */
   $sql_referendum = sprintf( "SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id);
  $result_referendum = mysql_query($sql_referendum, $con);
  print "<fieldset>";

if ($row = mysql_fetch_assoc($result_referendum)) {

  /* if the referendum is a draft or not started - start it */
  /* starting the referendum means changing the status to "in progress"  */

  if (strcmp($row['status'], "not started")==0){
  	

     
      $update_referendum = sprintf("UPDATE `referendum` SET `status`= 'in progress' WHERE `id` LIKE '%s';", $id);
     $result_update = mysql_query($update_referendum, $con);

/* success message */
    print "<h4>Referendum was successfully started.</h4></br></br>";
  }

else{

    /* if they got here, the status of the referendum is not draft or not started; therefore we cannot start it; terminate */
    die();
    
}

print "</fieldset>";
/* Back to main page button */


print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";

}

else {
   /* if they got here, this means that the referendum id could not be found in the database; terminate */
	die();
}

//close the connection to the database
mysql_close($con);
?>
</body>
</html>