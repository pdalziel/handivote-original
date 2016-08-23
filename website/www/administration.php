<?php 

/* 
Administrator dashboard, with links for creating new referendums, adding admins, logging out, all existing referendums
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/



include('session.php');
include('connection_db.php');
include('script.js');
include('css_files.php');


/* the following style settings are adjusted for this page */
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
  margin-left:30px;
}

fieldset
{
border:1px solid white;
width:30em;
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
margin-right: 30px;
display: inline-block
}

.submit input
{
margin-left: 4.5em;
}

</style>
</head>
<body>

<?php


/** welcome message and image **/

print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";
print "
<div><p>All referendums are listed below. Please note that <b>only 1 referendum can be live at any time </b>.<br/> Please schedule any new referendums after the end date of the previous one.</p></div>";


/* ADMIN OPTIONS */

/* Create New Referendum */
print"<fieldset>
<legend>Options</legend>
<form method='link' action='new_ref.php'>
<input type='submit' class='btn btn-success' value='Create New Referendum'>
</form>";

/* Add Another Admin */
print"<form method='link' action='add_admin.php'>
<input type='submit' class='btn btn-success' value='Add Another Admin'>
</form>";

/* Logout */
print"<br/><form method='link' action='logout.php'>
<input type='submit' class='btn btn-success' value='Logout'>
</form>";


print "</fieldset>";


/* Existing Referendums*/

/* table header*/    
print "
<br/><br/><br/>
<div>
<fieldset>
<legend>Existing Referendums</legend>
<table class='table table-striped table-bordered table-hover table-condensed'>
<tr>
<th>Description</th>
<th>Start Date</th>
<th>End Date</th>
<th>Votes</th>
<th>Status</th>
</tr>";

/* print 1 row for each referendum */
/* The title of each referendum is a link to view_referendum.php 
the ID of the referendum is passed to view_referendum.php via GET request
*/
$sql_referendum    = "SELECT * FROM referendum";
$result_referendum = mysql_query($sql_referendum, $con);
while ($row = mysql_fetch_assoc($result_referendum)) {

print "<tr>
<td><a href = \"view_referendum.php?id=".$row['id']."\">".$row['title']."</a></td>
<td>".$row['startDate']."</td>
<td>".$row['endDate']."</td>
<td>".$row['votes']."</td>
<td>".$row['status']."</td>
</tr>";

    }

print "</fieldset></form>";

//close the connection to the database
mysql_close($con);

?>
</body>
</html>