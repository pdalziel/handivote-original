<?php 

/* 
Displays the results of a referendum
Author: Tsvetelina Valcheva
Last Modified: 07/02/13
*/

include('connection_db.php');
include('sanitise.php');
include('script.js');
include('css_files.php');



?>

<!DOCTYPE html>


<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- this line optimises screen for smart phones-->
<meta name="HandheldFriendly" content="true"/> <!-- this line optimises screen for feature phones-->
<title>HandiVote</title>

<script type="text/javascript" src="javascript/dbscript.js"></script>
<script type="text/javascript"  src="javascript/jquery.js"></script>

<style type="text/css">     
  



body
{
  background-color: Beige;
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
margin: 0 auto;

}


</style>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">

      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {


<?php
$id = sanitize($_GET['id']);

$sql_question = sprintf("SELECT  * FROM `question` WHERE `refID` like '%s';", $id);
$result_question = mysql_query($sql_question, $con);
while ($row3 = mysql_fetch_assoc($result_question)){
  $qID = $row3['id'];


$sql = sprintf("select optionID,questionoption.option,count(distinct barcode) as cnt from vote,questionoption
where vote.refid='%s'
and questionoption.id=optionID
and vote.questionID = '%s'
and void = '0'
group by optionID;", $id, $qID);

$result = mysql_query($sql,$con);

$title = $row3['question'];
  


?>

        var data = google.visualization.arrayToDataTable([
<?php
print "['Name','Percent of Votes'],";
while ($row = mysql_fetch_assoc($result)){
$opt = $row['option'];
$cnt = $row['cnt'];

print "['".$opt."',".$cnt."],";
}
        print "]);";

?>
        var options = {
        title: <?php print "'".$title."'";  ?>,
        };

        var chart = new google.visualization.PieChart(document.getElementById(<?php print "'pie_chart_div".$qID."'";  ?>));
        chart.draw(data, options);

<?php $result = mysql_query($sql,$con);?>
 var data = google.visualization.arrayToDataTable([
<?php
print "['Name','Number of Votes'],";
while ($row = mysql_fetch_assoc($result)){
$opt = $row['option'];
$cnt = $row['cnt'];
print "['".$opt."',".$cnt."],";
}
        print "]);";

?>


  var options = {
        title: <?php print "'".$title."'";  ?>,
        vAxis: {format:'#'}

        };

        var chart = new google.visualization.ColumnChart(document.getElementById(<?php print "'bar_chart_div".$qID."'";  ?>));
        chart.draw(data, options);

        <?php } ?>
      
      
/* custom chart for the Budgetary Referendum */      
/*
var data = google.visualization.arrayToDataTable([['Name','Number of Votes'],['I support the division of S101 (existing Common Room) to accommodate two additional academic offices, re-siting the existing Common Room in a more central location at F171',28],['I do not support any change to the existing Common Room space.',17],['I support the division of S101 (existing Common Room) to accommodate one additional academic office, with a smaller Common Room',5],['Come up with some other suggestions and I will consider them',3]]); 
var options = {

        title: 'How should any spare funding be allocated?',
        };
var chart = new google.visualization.ColumnChart(document.getElementById('pie_chart_div5'));
        chart.draw(data, options);
        
        */  
 /* 
var data = google.visualization.arrayToDataTable([['Name','Percent of Votes'],['I support the division of S101 (existing Common Room) to accommodate two additional academic offices, re-siting the existing Common Room in a more central location at F171',52.83],['I do not support any change to the existing Common Room space.',32.07],['I support the division of S101 (existing Common Room) to accommodate one additional academic office, with a smaller Common Room',09.43],['Come up with some other suggestions and I will consider them',05.66]]); 
var options = {

        title: 'How should any spare funding be allocated?',
        };
var chart = new google.visualization.PieChart(document.getElementById('pie_chart_div6'));
        chart.draw(data, options);  
  
  */
  
  
     
    }  


    </script>
</head>


<body>
<?php


print "<div class='page-header'><h2>Welcome to <a href='index.php'><img src='handivote.jpg' width='120' height='80'></a> Results<br>";

   $sql = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id );
  $result = mysql_query($sql, $con);

/** If a referndum is found, print its title, if no referendum is found, print a message and die **/
if ($row = mysql_fetch_assoc($result)) {
    print "<h3> >> ".$row['title']." << </h3>";
  }
print "<table align='center' id='showtable'><tr>";
print "<td rowspan=2><input type='image' src='newcard.png' width='200' height='150'></td>";
print "<td style='padding:10px;border: solid black 2px;background-color:#ffcc99;'>";

print "<input type='submit' value='";
print "Would you like to Check that your Vote was Recorded Correctly?\n";
print "Click Here to Check'";
print "  style='font-size:1.2em; '";
print " onclick='this.style.display=\"none\";document.getElementById(\"makeappear\").style.display=\"inline\";document.getElementById(\"bottomrow\").style.display=\"none\";'>";

print "<div id='makeappear' style='display:none;'>";

print "Card Number:<input type='text' id='cardnumber'>";
print "<div id='verifydiv' style='display:none;'>";
print "<br>Your Vote:<input type='text' id='verify'>";
print "</div>";

print "<br><input type='submit' value='";
print "Click here to Check Your Vote and see the Results'";
print "  style='font-size:1.2em;' onclick='trust(0,$id);document.getElementById(\"showarea\").style.display=\"block\";document.getElementById(\"verifydiv\").style.display=\"inline\";document.getElementById(\"bottomrow\").style.display=\"none\";'></div></td>";
print "</div>";
print "</td></tr><br>";


print "<tr id='bottomrow'><td align='center'  style='background-color:#ffff99;border:2px solid;padding: 10px;'>
 ";
print "<input type='submit' value='That is OK, I trust Handivote\n Click here to see the Results Without Checking' onclick='trust(1,0);document.getElementById(\"showarea\").style.display=\"block\";document.getElementById(\"showtable\").style.display=\"none\";document.getElementById(\"oops\").style.display=\"block\";' style='font-size:1.2em;'></td>";
print "</tr></table>";

print "<div id='oops' style='display:none;'>";
print "<a href='view_result.php?id=10' style='text-decoration:none;'> <input type='submit' value='I would like to Go Back and Check my Vote' style='font-size:0.8em;'></a>";
print "</div>";

print "</h2></div><div id='showarea' style='display:none;'>";


 /** Get the referendum to be published **/

$id = sanitize($_GET['id']);

   $sql = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id );
  $result = mysql_query($sql, $con);

/** If a referndum is found, print its title, if no referendum is found, print a message and die **/
if ($row = mysql_fetch_assoc($result)) {
  if ((strcmp($row['status'], "calculated_result")==0) || (strcmp($row['status'], "published")==0)){
    print "<h3> >> ".$row['title']." << </h3>";
  }

  else{
  die("Referendum cannot be published");	
  }

}
else {
  die("Nothing to pubish at the moment.");
}


/****** Displaying the results *******/
print "<fieldset>";
print "<h3 style = 'color:DarkRed'>Results</h3><br/>";



 $sql_question = sprintf("SELECT  * FROM `question` WHERE `refID` like '%s';", $id);
 	$result_question = mysql_query($sql_question, $con);
$counter = 1;

/** print each question **/
	while ($row1 = mysql_fetch_assoc($result_question)) {
		$qID = $row1['id'];

		print "<fieldset>";
		print "<br/><h4 style = 'color:SaddleBrown'>Question ".$counter.": ".$row1['question']."</h4>";

		$counter +=1;


if ($row1['type']=='simple'){
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
        		print "<h4 style = 'color:Green'>Highest Number Votes: <b>".$row3['option']."</b></h4>";
        	}

        }

      }

else if ($row1['type']=='tax' or $row1['type']=='spend' ){
print "<h4 style = 'color:Green'>Average Value according to votes: <b>".number_format(($row1['average']*100),2)."</b> %</h4>";

print "<h4 style = 'color:Green'>Feasible Value after balancing the budget: <b>".number_format(($row1['realisable']*100),2)."</b> %</h4>";



}

 print "<br/><br/><h4>Referendum Statistics</h4>";
/**
print "
    <div id='pie_chart_div5' style='width: 600px; height: 450px;'></div>
        ";


print "
    <div id='pie_chart_div6' style='width: 600px; height: 450px;'></div>
        ";

**/
if ($id == 7)
print "<img src='meadowresults.png'>";
else {

if ($id == 10)
print "<img src='neilston.png'>";
else {

 print "
 <table><tr><td>
    <div id='pie_chart_div".$qID."' style='width: 540px; height: 350px;'></div>
        </td><td>
   <td><div id='bar_chart_div".$qID."' style='width: 540px; height: 350px;'></div>
        </td></tr></table>
";
}}

        /** Print the Results Breakdown **/
        print "<br/><br/><br/><h4>Results Breakdown</h4>";
        print "<table class='table table-striped table-bordered table-hover table-condensed' >";
        print "<tr><th>#</th><th>Option</th><th>Votes </th><th>Barcodes</th></tr>";

        /****** print the options sorted, highest number of votes first *******/
        /** get all the options with the corresponding number of votes **/
        
        $sql_get_votes = sprintf("SELECT `number_votes`, `optionID` FROM `results` WHERE `refID` LIKE '%s' AND `qID` LIKE '%s' ORDER BY `number_votes` DESC;", $id, $qID);

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
            <td><input type = 'button' class= 'btn btn-success' value='Show Card Numbers' onClick='window.location=\"show_barcode.php?optionID=".$optionID."\";'></td></tr>";

            $option_counter+=1;
            }

		    print "</table>";
		    print "</fieldset>";
	     }

// fix this - only print this if any of questions tax or spend
//print "<fieldset><h4 style = 'color:Green'>Degree of difference between average vote and feasible balanced budget result: <b>".number_format(($row['degree'])*100,2)."%</b> </h4><h5>(This value is equal to 0 when all votes are consistent with balanced budget. The higher the value, the larger the difference between the actual vote and the closest to the actual vote feasible result.)  </h5></fieldset>";

 /** get all voided votes **/
 $sql_voided = sprintf("SELECT count(DISTINCT `barcode`) AS voided FROM `vote` WHERE `void` LIKE '1' AND `refID` LIKE '%s';", $id);

 $result_voided = mysql_query($sql_voided, $con);
 $row4 = mysql_fetch_assoc($result_voided);
 $voided = $row4['voided'];
 
 /** get all votes, excluding voided **/
  $sql_total = sprintf("SELECT count(distinct `barcode`) AS total FROM `vote` WHERE `void` LIKE '0' AND `refID` LIKE '%s';", $id);

 $result_total = mysql_query($sql_total, $con);
 $row5 = mysql_fetch_assoc($result_total);
 $total = $row5['total'];

  /** get all abstain votes **/
  $sql_abstain = sprintf("SELECT count(DISTINCT `barcode`) AS abstain FROM `vote` WHERE `optionID` LIKE '233' AND `refID` LIKE '%s';", $id);
 $result_abstain = mysql_query($sql_abstain, $con);
 $row6 = mysql_fetch_assoc($result_abstain);
 $abstain = $row6['abstain'];

  /* get all votes who failed credentials or are in bad_vote */

$sql_failed = sprintf("SELECT count( DISTINCT `barcode` ) AS failed FROM `bad_vote` WHERE `type` like  'failed_credentials' and `refID` LIKE '%s';",$id);
 $result_failed = mysql_query($sql_failed, $con);
 $row6 = mysql_fetch_assoc($result_failed);
 $failed = $row6['failed'];

/** print the total votes and the voided votes in a table **/
print "<table class='table table-bordered table-hover'>";
print " <tr><td><h4>Total Valid Votes: <font color='red'>".$total."</font></h4></td><td> <input type = 'button' class= 'btn btn-success' value='Show Card Numbers' onClick='window.location=\"show_barcode.php?optionID=total&redID=".$id."\";'></td></tr>";
print " <tr><td><h4>Abstain Votes: <font color='red'>".$abstain."</font></h4></td><td> <input type = 'button' class= 'btn btn-success' value='Show Card Numbers' onClick='window.location=\"show_barcode.php?optionID=233&redID=".$id."\";'></td></tr>";

print "<tr><td><h4> Discarded Duplicate Votes: <font color='red'>".$voided." </font></h4></td><td><input type = 'button' class= 'btn btn-success' value='Show Card Numbers' onClick='window.location=\"show_barcode.php?optionID=voided&redID=".$id."\";'></td></tr><br/>";

print "<tr><td><h4> Discarded votes due to failure to verify the credentials*:<font color='red'> ".$failed." </font></h4></td><td><input type = 'button' class= 'btn btn-success' value='Show Card Numbers' onClick='window.location=\"show_barcode.php?optionID=failed&redID=".$id."\";'></td></tr><br/>";

print "</table>";
print "</fieldset>";
print "* We could not verify your  HandiVote Card Number</div>";

mysql_close($con);

?> 
</body>
</html>
