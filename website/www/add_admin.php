<?php

/* 
Page to add administrator, contains form to be submitted with the administrator details
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


include('session.php');
include('sanitise.php');
include('connection_db.php');
include('script.js');



/* PROCESS INPUTS FROM DETAILS FORM */

/* Input Validation */


/* global boolean variables, used for input validation*/
$GLOBALS['errors']= false;  
$GLOBALS['existing_username']= false;


if (isset($_POST['submit'])){    // if the submit button was pressed

    /* if any of the fields are left empty, will output error message */
	if (empty($_POST['firstname']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['username']) || empty($_POST['password'])){
		$GLOBALS['errors']= true;
    }

    $username = sanitize($_POST['username']);

    /* chech if the username is already in the database */

    $admin_username   = "SELECT `username` FROM `admin`";
    $result = mysql_query($admin_username, $con); 
    if (!$result) {die('Error: ' . mysql_error());}       

    while ($row = mysql_fetch_assoc($result)){
            if (strcmp($username, $row['username'])==0) {
                $GLOBALS['existing_username']= true;
                }
           }

/* IF input is valid (all fields are filled in, and username is not taken), continue with processing */
if ((!$GLOBALS['errors'] && !$GLOBALS['existing_username'])) {

    $firstname = sanitize($_POST['firstname']);
    $surname = sanitize($_POST['surname']);
    $password = sanitize($_POST['password']);
    $email = sanitize($_POST['email']);


          /* Insert the admin details to the database */
 
    $insert_admin   = sprintf("INSERT INTO `handivote`.`admin` (`id`, `username`, `password`, `firstname`, `surname`, `email`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s');", $username, $password, $firstname, $surname, $email);

    $result_insert = mysql_query($insert_admin, $con);    
  
    	/* display thank you message */
	 header("Location:success_add_admin.php");
   die(); // terminate processing
        }


}

?>


<!DOCTYPE html>
<html>
<head>
<title>HandiVote</title>
<?php
include('css_files.php'); /* include statement has to be here because HTML inside file interferes with header() call above*/
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
border:1px solid white;
width:23em;
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

label
{
width: 6em;
float: left;
text-align: right;
margin-right: 30px;
display: inline-block
}

.submit input
{
margin-left: 4.5em;
margin-left: 4.5em;
}
.divider{
    width:5px;
    height:auto;
    display:inline-block;
}
</style>
</head>
<body>



<?php

/******** Collect input from the user *************/

/*title*/
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";
print "<h3>Add Administrator</h3></br>";

/* if input already provided, retain it and populate the form */
 if (isset($_POST['firstname'])){
     $firstname = $_POST['firstname'];}
 else{ $firstname = "";}

if (isset($_POST['surname'])){
     $surname = $_POST['surname'];}
 else{ $surname = "";}

if (isset($_POST['email'])){
     $email = $_POST['email'];}
 else{ $email = "";}

if (isset($_POST['username'])){
     $username = $_POST['username'];}
 else{ $username = "";}

 if (isset($_POST['password'])){
     $password = $_POST['password'];}
 else{ $password = "";}


/* INPUT FORM FOR ADMIN DETAILS */
print "<form name='add_admn' method= 'post' action='" . htmlentities($_SERVER['PHP_SELF']) . "' class='form-inline'>";
print "<fieldset><legend> Please fill in the details</legend>";
print " <p><label for='firstname'>First Name:*</label> <input type='text' name='firstname' id='firstname' value = '".$firstname."' /></p>
<p><label for='surname'>Surname:*</label> <input type='text' name='surname' id='surname' value = '".$surname."'/></p>
<p><label for='email'>Email:*</label> <input type='text' name='email' id='email' value = '".$email."'/> </p>
<p><label for='username'>Username:*</label> <input type='text' name='username' id='username' value = '".$username."'/></p>
<p><label for='password'>Password:*</label> <input type='password' name='password' id='password' value = '".$password."'/></p>";

 /** if errors were found in the input, display this message **/
if ($GLOBALS['errors']){
      print "<br/><b style = 'color:red'>Please fill in all fields.</b>";
}

/* if username already taken, display this message */
if ($GLOBALS['existing_username']){
      print "<br/><b style = 'color:red'>Please select another username</b>";
}

 /** back button, takes user to administration.php **/
print"</br></br><INPUT TYPE='button' VALUE='Back' class='btn btn-success' onClick='window.location=\"administration.php\";'>";
print "<div class='divider'></div>";

 /** submit button **/
print "<input type='submit' class='btn btn-success' name='submit' value = 'Submit Details'/></form></br>";
print "</fieldset></form>";

//close the connection to the database
mysql_close($con);

?>
</body>
</html>
