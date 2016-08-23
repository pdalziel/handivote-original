<?php
/* 
Page to display a list of barcodes that voted for particular option
expects the option ID via GET
Author: Tsvetelina Valcheva
Last Modified: 29/01/13
*/

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- this line optimises screen for smart phones-->
<meta name="HandheldFriendly" content="true"/> <!-- this line optimises screen for feature phones-->
<title>HandiVote</title>

<script type="text/javascript" src="javascript/dbscript.js"></script>
<script type="text/javascript"  src="javascript/jquery.js"></script>
<?php
include ('connection_db.php');
include('css_files.php');
include('sanitise.php');
?>



<style type="text/css">     
body
{
  background-color: Snow;
  margin-top:10px;
  margin-left:10px;
}

</style>
</head>


<body>

<div id="question2">
<h2>Please would you help us by saying<br> what your main motivation
was in visiting this page</h2>
<form action="" method="post" onSubmit="return storeAnswer(2,'answer2');">
<h2>

<textarea id="answer2" style="width:800px;height:100px;">
</textarea><br>
<input type="submit" value="Click here to Submit your Answer
(will not open a new page)" style="width:450pt;font-size:1.0em;">
</form>
</div>
<div id="thanks2" style="display:none;">
<img src="thank_you_happyface.jpg">
</div>
<table><tr><td>

<td rowspan=2><input type='image' src='newcard.png' width='200' height='150'>
</td><td>
<?php

$optionID = sanitize($_GET['optionID']);
$sql_getdetails="select * from questionoption where id=".$optionID;
$result_barcodes = mysql_query($sql_getdetails, $con);

while ($row = mysql_fetch_assoc($result_barcodes)){
       print "<h1>Card Numbers for option: ".$row['option']."</h1>";
    }
?>

<h2 style="color:red;">Look for the first 4 digits of your card number</h2>
</td>
</tr></table>
<?php

/* get the option ID and a referendum ID if one is supplied */
$optionID = sanitize($_GET['optionID']);
if (isset($_GET['redID'])){
$refID = sanitize($_GET['redID']);
}

/**** Process the request to display barcodes ****/
$sql_get_barcodes = '';

/* DISTINCT is used because a referendum with more than one question will have more than one entries in vote - one entry for each question */

/** request for voided bacodes (repeat vote) **/
 if (strcmp($optionID, 'voided') ==0){
   $sql_get_barcodes = sprintf("SELECT DISTINCT `barcode` FROM `vote` WHERE `void` LIKE '1'AND `refID` LIKE '%s' ORDER BY `barcode`;",$refID);

}

/** request for total barcodes **/
  else if (strcmp($optionID, 'total') ==0){
   $sql_get_barcodes = sprintf("SELECT DISTINCT `barcode` FROM `vote` WHERE `void` LIKE '0'AND `refID` LIKE '%s' ORDER BY `barcode`;",$refID);
}


/** request for bad_vote  failed credentials barcodes (excludes repeat vote) **/
  else if (strcmp($optionID, 'failed') ==0){
   $sql_get_barcodes = sprintf("SELECT DISTINCT `barcode` FROM `bad_vote` WHERE `type` like 'failed_credentials' AND `refID` LIKE '%s' ORDER BY `barcode`;", $refID);
}

/** request for barcodes for specific option **/
else {
  $sql_get_barcodes = sprintf("SELECT DISTINCT `barcode` FROM `vote` WHERE `void` LIKE '0' AND `optionID` LIKE '%s' ORDER BY `barcode`;", $optionID);
 }
 

/** get the barcodes from the database and print them **/
$result_barcodes = mysql_query($sql_get_barcodes, $con);

while ($row = mysql_fetch_assoc($result_barcodes)){
       print $row['barcode']."<br/>";
    }
        

mysql_close($con); // close the connection to the database
?>
</body>
</html>
