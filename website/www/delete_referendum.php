<?php 
/* 
Page to delete referendum, the referendum ID is supplied via GET request
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
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

/* page expects referendum ID via GET request, if none provided, terminate with error message */
if (empty($_GET['id'])){
  die("Access Denied");
}

  $id = sanitize($_GET['id']);

  /* take the referendum from the database */
  $sql_referendum    = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id );
  $result_referendum = mysql_query($sql_referendum, $con);

  print "<fieldset>";

  if ($row = mysql_fetch_assoc($result_referendum)) {

  /* if the referendum is a draft or not started - delete it */

    if (strcmp($row['status'], "draft")==0 || strcmp($row['status'], "not started")==0){
  	
      $delete_referendum = sprintf("DELETE FROM `referendum` WHERE `id` LIKE '%s';", $id);
      $result_delete = mysql_query($delete_referendum, $con);

      /* delete the questions from this referendum */
      $delete_questions = sprintf("DELETE FROM `question` WHERE `refID` like '%s';", $id);
      $delete = mysql_query($delete_questions);
      
      /* delete the options for each question */
      $delete_options = sprintf("DELETE FROM `questionoption` WHERE `refID` like '%s' ;", $id);
      $delete = mysql_query($delete_options);
      
      /* success message */
      print "<h4>Referendum was successfully deleted.</h4></br></br>";
    }

    /* if the referendum was not a draft or has already started, terminate with an error message */
    else{
      die("Access Denied");    
    }

print "</fieldset>";

/* Back to main page button */
print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";

}

/* if we get here the referendum could not be found in the database - terminate with an error message */
else {
	die("Access Denied");
}

//close the connection to the database
mysql_close($con);

?>
</body>
</html>