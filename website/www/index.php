<?php 

/* 
This is the initial page voters arrive at; It displays a link to the currently running referendum, if there is one; It also provides links to the results of
past referendums 
Author: Tsvetelina Valcheva
Last Modified: 29/01/13
*/


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
width: 20em;
float: left;
text-align: left;
margin-right: 10px;
display: inline-block
}

.submit input
{
margin-left: 10px;
}

</style>
</head>
<body>

<?php




/** welcome message and image **/

print "<div class='page-header'><h2>Welcome to <a href='http://www.dcs.gla.ac.uk/handivote'>' <img src='handivote.jpg' width='120' height='80'></a>";
print "</h2></div>";

/* only one referendum is ever in progress; fetch it from the database */
$sql_current_referendum    = "SELECT * FROM `referendum` WHERE `status` LIKE 'in progress'";
$result_current_referendum = mysql_query($sql_current_referendum, $con);

/* display current referendum */
print"<fieldset>
<legend>Current Referendum</legend>";

if ($row = mysql_fetch_assoc($result_current_referendum)) {

/* this is a link to the currently running referendum */
print "
<form method='link' action='handivote.php'>
<label for=\"vote\"><h4>".$row['title']."</h4></label>
<input type='submit' class='btn btn-success btn-large' value='Vote Here' id= 'vote'>
</form>";


}
else{
/* if the query was empty - no referendum is currently running, display only a message, no link */
print "<p>No referenda currently running</p>";
}

print "</fieldset>";
/* Previous Referendums*/

/* table header */    
print "
<br/><br/><br/>
<div>
<fieldset>
<legend>Previous Referenda</legend>
<table class='table table-striped table-bordered table-hover table-condensed'>";

/* print 1 row for each referendum */
/* The title of each referendum is a link to view_result.php 
the ID of the referendum is passed via GET to view_result.php */
$sql_referendum    = "SELECT * FROM `referendum` WHERE `status` LIKE 'published' order by id desc";
$result_referendum = mysql_query($sql_referendum, $con);
while ($row = mysql_fetch_assoc($result_referendum)) {

print "<tr>
<td><a href = \"view_result.php?id=".$row['id']."\">".$row['title']." --- Ended: ".$row['endDate']."</a></td>
</tr>";

    }

print "</fieldset></form>";

mysql_close($con); // close connection to database

?>
</body>
</html>
