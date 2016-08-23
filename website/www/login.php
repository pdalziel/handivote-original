<?php

/* 
Login page, users are redirected to this page whenever they attempt to access one of the administration pages
Author: Tsvetelina Valcheva
Last Modified: 29/01/13

For the authentication implementation with session variables I looked at code in here: 
*/


include('sanitise.php');
include('connection_db.php');
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
  margin-left:10px;
}

fieldset
{
border:1px solid white;
width:25em;
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
width: 8em;
float: left;
text-align: right;
margin-right: 15px;
display: inline-block
}

.submit input
{
margin: 15px auto;
}

</style>
</head>
<body>

<?php


/* authentication is implemented with session variables */

session_start();

if (!isset($_SESSION['username'])) // if the user has not already authenticated
{
	/* display title */
	print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
    print "</h2></div>";

    /* login form */
    /* request username and password */
	print "<form name='login' method= 'post' action='" . htmlentities($_SERVER['PHP_SELF']) . "' class='form-inline'> 
	<fieldset><legend>Please log in:</legend>
	<p><label for='username'>Username:</label> <input type='text' name='username' id='username' required /> </p>
	<p><label for='password'>Password:</label> <input type='password' name='password' id='password' required /></p>
	<br/><div><input type= 'submit' class='btn btn-success' value='Login'/></div></fieldset>";

   /* check is username-password combination in the database */

    if(isset($_POST['username'])){ 

	$username=sanitize($_POST['username']);
	$password=sanitize($_POST['password']);

	$sql_login = "SELECT * FROM admin ";
	$result = mysql_query($sql_login);
	while ($row=mysql_fetch_assoc($result))
	{
        /* if user name and password are discovered in the database, set the session variables to the username and password
         and transfer the user to the administration.php page */
		if($row['username']==$username && $row['password']==$password){
		    $_SESSION['username'] = $username;
		    $_SESSION['password'] = $password;
		    header("location:administration.php");
		}
	}
    /* credentials were not discovered in the database, output message */

    print  "<b style = 'color:red'>Login failed. Please try again.</b>";

    }
}



mysql_close($con); // close connection to the database
?>

</body>
</html>