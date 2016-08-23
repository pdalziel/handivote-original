<?php 
include('session.php');
include('connection_db.php');
?>

<!DOCTYPE html>


<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- this line optimises screen for smart phones-->
<meta name="HandheldFriendly" content="true"/> <!-- this line optimises screen for feature phones-->
<title>HandiVote</title>

<?php
include('script.js');
include('css_files.php');
?>



<style type="text/css">     
  


body
{
  background-color: Snow;
  margin-top:1px;
  margin-left:1px;
   text-align:center;

}

fieldset
{
border:1px solid white;
width:50em;
font-size: 110%;
background-color: Beige;
padding: 1px;
margin:auto;
}

div
{
margin-top: 1px;
margin-bottom: 1px;
margin-left:10px;
}

</style>
</head>


<body>
<?php


print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";


 /** Get the referendum to be published **/

 if (empty($_GET['id'])){
  die("Access Denied");
}

 $id = $_GET['id'];

   $sql = "SELECT * FROM `referendum` WHERE `id` LIKE '" . $id . "'";
 // $sql    = "SELECT * FROM `referendum` WHERE `status` LIKE 'publish'";
  $result = mysql_query($sql, $con);
 // $id = '';

/** If a referndum is found, print its title, if no referendum is found, print a message and die **/
if ($row = mysql_fetch_assoc($result)) {
  if ((strcmp($row['status'], "calculated_result")==0) || (strcmp($row['status'], "published")==0)){
    print "<h3> >> ".$row['title']." <<</h3>";
  }

  else{
  die("Referendum cannot be published");	
  }
	//$id = $row['id'];
}
else {
  die("Nothing to pubish at the moment.");
}


/****** Displaying the results *******/
print "<fieldset>";
print "<h3 style = 'color:DarkRed'>Results</h3>";

 $sql_question    = "SELECT * FROM `question` WHERE `refID` LIKE '" . $id . "'";
 	$result_question = mysql_query($sql_question, $con);
$counter = 1;

/** print each question **/
	while ($row1 = mysql_fetch_assoc($result_question)) {
		$qID = $row1['id'];

		print "<fieldset>";
		print "<h4 style = 'color:SaddleBrown'>Question ".$counter.": ".$row1['question']."</h4>";

		$counter +=1;

    /** print the winning option or options for that question **/
    /** get the id for the winning option(s)**/
		$sql_get_winner = "SELECT `optionID` FROM `results` WHERE `winner` LIKE '1' AND `qID` LIKE ".$qID;
    $result_winner = mysql_query($sql_get_winner, $con);

    while ($row2 = mysql_fetch_assoc($result_winner)){
        	$winning_option = $row2['optionID'];

          /** get the text of the winning option(s) and print it **/
        	$sql_text_winning_option = "SELECT `option` FROM `questionoption` WHERE `id` LIKE ".$winning_option;
		    	$result_winner_text = mysql_query($sql_text_winning_option, $con);
        	if ($row3 = mysql_fetch_assoc($result_winner_text)){
        		print "<h4 style = 'color:Green'>Winner is: <b>".$row3['option']."</b></h4>";
        	}

        }
        /** Print the Results Breakdown **/
        print "<h4>Results Breakdown</h4>";
        print "<table class='table table-striped table-bordered table-hover table-condensed' >";
        print "<tr><th>#</th><th>Option</th><th>Votes </th><th>Barcodes</th></tr>";

        /****** print the options sorted, highest number of votes first *******/
        /** get all the options with the corresponding number of votes **/
        $sql_get_votes = "SELECT `number_votes`, `optionID` FROM `results` WHERE `refID` LIKE '".$id."' AND `qID` LIKE '".$qID."' ORDER BY `number_votes` DESC";
        $result_votes = mysql_query($sql_get_votes, $con);
        $option_counter = 1;

        /** for every option, get the id and the number of votes **/
        while ($row4 = mysql_fetch_assoc($result_votes)){

            $optionID = $row4['optionID'];
            $number_votes = $row4['number_votes'];

            /** get the text of the option **/
            $sql_options = "SELECT `option` FROM `questionoption` WHERE `id` LIKE ".$optionID;
            $result_option = mysql_query($sql_options, $con);
            $row5 = mysql_fetch_assoc($result_option);

            $optionText = $row5['option'];
      
            /** print the option in a table raw **/
            print "<tr><td>".$option_counter.".</td><td>".$optionText."</td><td>".$number_votes."</td>
            <td><input type = 'button' class= 'btn btn-success' value='Show' onClick='window.location=\"show_barcode.php?optionID=".$optionID."\";'></td></tr>";

            $option_counter+=1;
            }

		    print "</table>";
		    print "</fieldset>";
	     }


 /** get all voided votes **/
 $sql_voided = "SELECT count( * ) AS voided FROM `vote` WHERE `void` LIKE '1' AND `refID` LIKE ".$id;
 $result_voided = mysql_query($sql_voided, $con);
 $row4 = mysql_fetch_assoc($result_voided);
 $voided = $row4['voided'];
 
 /** get all votes, including voided **/
 $sql_total = "SELECT count( * ) AS total FROM `vote` WHERE `void` LIKE '0' AND `refID` LIKE ".$id;
 $result_total = mysql_query($sql_total, $con);
 $row5 = mysql_fetch_assoc($result_total);
 $total = $row5['total'];

/** print the total votes and the voided votes in a table **/
print "<table class='table table-bordered table-hover'>";
print "<tr><td><h4> Discarded Duplicate Votes: ".$voided." </h4></td><td><input type = 'button' class= 'btn btn-success' value='Show' onClick='window.location=\"show_barcode.php?optionID=voided&redID=".$id."\";'></td></tr><br/>";
print " <tr><td><h4>Total Valid Votes: ".$total."</h4></td><td> <input type = 'button' class= 'btn btn-success' value='Show' onClick='window.location=\"show_barcode.php?optionID=total&redID=".$id."\";'></td></tr>";
print "</table>";
print "</fieldset>";

/* update the status of the referendum to "published"*/
$update_ref = "UPDATE `referendum` SET `status`= 'published' WHERE `id` LIKE ".$id;
$result_update = mysql_query($update_ref, $con);

mysql_close($con);

?> 
</body>
</html>
