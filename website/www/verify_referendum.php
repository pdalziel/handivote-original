<?php 

/* 
Displays a summary of the details of a draft referendum, lets the admin verify these details are correct
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/
include('session.php');
include('connection_db.php');

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
  margin-left:100px;
}

fieldset
{
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
.divider{
    width:5px;
    height:auto;
    display:inline-block;
}

</style>
</head>
<body>
<?php
/*title*/
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";

/* referendum id expected via GET, if none supplied, terminate */
if (empty($_GET['id'])){
  die("Access Denied");
}
  $id = $_GET['id'];

print "<h3>Please verify this referendum:</h3>";
print "<fieldset>";
 
 /* get the referendum*/
   $sql_referendum    = "SELECT * FROM `referendum` WHERE `id` LIKE '" . $id . "'";
    $result_referendum = mysql_query($sql_referendum, $con);

    if (!$result_referendum) {
    die('Could not connect: ' . mysql_error());
    }
/* id could not be found in the database, terminate */
    if (mysql_num_rows($result_referendum)==0){
        die("Invalid Input");
    } 

    while ($row = mysql_fetch_assoc($result_referendum)) {

        

/** If referendum  is not a "draft", terminate (only draft referendums are verified by this page) **/
        if (strcmp($row['status'], "draft")!=0){
            die("Invalid Input");
        }
    
    /* print the details of the draft referendum */
    	print "<h4>" . $row['title'] . "</h4>";
        print "<b>Start Date: </b>".$row['startDate']."<br/>";
    	print "<b>End Date: </b>".$row['endDate']."<br/><br/>";

       
 /* quesitons */
         $sql_question    = "SELECT * FROM question WHERE refID=" . $id;
            $result_question = mysql_query($sql_question, $con);

            $question_counter = 1;

/* options */
             while ($row2 = mysql_fetch_assoc($result_question)) {             
                $idQ = $row2['id'];

                print "<b>Question ".$question_counter.": </b>".$row2['question']."<br/>";

                $sql_option    = "SELECT * FROM questionoption WHERE qID=" . $idQ;
                $result_option = mysql_query($sql_option, $con);

                $option_counter = 1;
                while ($row3 = mysql_fetch_assoc($result_option)) {
                   
                	print "<b>Option ".$option_counter.": </b>". $row3['option']."<br/>"; 
                     $option_counter+=1;

                }

/* depending on type of question, there might be more information to be displayed */
                if (strcmp($row2['type'], 'spend')==0){
                    print "<b>Current spending :</b> ".$row2['current_spend']." Pounds. (<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/>";

                }
                if (strcmp($row2['type'], 'tax')==0){
                    print "<b>Current tax level :</b> ".$row2['current_tax']."%. (<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/>";
                    print "<b>Current income from tax in pounds :</b> ".$row2['current_tax_pound'].". (<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/>";
                }

                $question_counter+=1;
                print "<br/>";

             }

    /** Display the Revenue, if no revenue supplied skip this section **/         
    $revenue = $row['revenue'];
    if ($revenue!= 0){

        print "<b>Revenue</b> in the next period will be ".$revenue."(<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/><br/>";
         } 

$spending = $row['spending'];

/** Display the Spending, if no spending supplied skip this section **/ 
    if ($spending!= 0){
    

        print "<b>Spending</b> in the next period will be ".$spending."(<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/><br/>";
   } 
}

print "</fieldset>";

//back button, goes to main admin page

print"<INPUT TYPE='button' VALUE='Back' class= 'btn btn-success' onClick='history.go(-1);'>";

print "<div class='divider'></div>";

//button create referendum, goes to successful_referendum.php, passes referendum id via GET
print"<INPUT TYPE='button' VALUE='Create Referendum' class= 'btn btn-success' onClick='window.location=\"successful_referendum.php?id=".$id."\";'>";

// close connection to the database
mysql_close($con);


?>