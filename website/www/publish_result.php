<?php 

/* 
Page to publish the result of the referendum
referendum id is passed to this page via GET
Author: Tsvetelina Valcheva
Last Modified: 29/01/13
*/



include('session.php');
include('script.js');
include('connection_db.php');
include('sanitise.php');
include('css_files.php');
?>


<!DOCTYPE html>
<html>
<head>
<title>HandiVote</title>
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


/* referendum id is expected via GET, if none received, terminate with an error message */
if (empty($_GET['id'])){
  die("Access Denied");
}

  $id = sanitize($_GET['id']);

/* get the referendum from the database */
   $sql_referendum    = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id);
  $result_referendum = mysql_query($sql_referendum, $con);


  print "<fieldset>";

if ($row = mysql_fetch_assoc($result_referendum)) {

  /* if the status of the referendum is calculated_result, then we can publish it 
  to publish a referendum we only need to change its status to "published" */

  if (strcmp($row['status'], "calculated_result")==0){
  	
     $update_referendum = sprintf("UPDATE `referendum` SET `status`= 'published' WHERE `id` LIKE '%s';", $id);

     $result_update = mysql_query($update_referendum, $con);

    /* success message*/
    print "<h4>Referendum was successfully published.</h4></br></br>";
  }

else{

    /* if they got here, this means that the status was different from calculated_result - therefore we cannot publish the result
    terminate with an error message  */
    die("Access Denied");
    
}

print "</fieldset>";


/* Back to main page button -  */


print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";

}

else {
   /* if they got here, the referendum with this ID was not found in the database, terminate with an error  */
	die("Access Denied");
}

//close the connection to the database
mysql_close($con);
?>
</body>
</html>