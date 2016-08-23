<?php 

/* 
Displays a message informing user that new admin was added successfully; 
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/

include('session.php');
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
  margin-left:10px;
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
/* title*/
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";

/* message */
print "<fieldset>";
print "<h4>Thank you. The admin was successfully added.</h4></br></br>";

/* button back to main admin page */
print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";
print "</fieldset>";


?>

</body>
</html>