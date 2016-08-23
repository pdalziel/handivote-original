<?php

/* 
Displays a summary of the details of a referendum
Author: Tsvetelina Valcheva
Last Modified: 07/02/13
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
border:1px solid white;
width:35em;
margin-top: 5px;
margin-bottom: 10px;
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


  $id = sanitize($_GET['id']);

$sql_referendum = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id);

    $result_referendum = mysql_query($sql_referendum, $con);

    if (!$result_referendum) {
    die('Could not connect: ' . mysql_error());
    }
/** If someone decides to manually type in a non-existent referendum ID or empty input, terminate everything **/
    if (mysql_num_rows($result_referendum)==0){
        die("Invalid Input");
    } 

/* print the details of the referendum */

    while ($row = mysql_fetch_assoc($result_referendum)) {
  
    	print "<h3>" . $row['title'] . "</h3>";
    	print "<fieldset>";
    	print "<b>Status: </b>".$row['status']."<br/>";
        print "<b>Start Date: </b>".$row['startDate']."<br/>";
    	print "<b>End Date: </b>".$row['endDate']."<br/>";
        print "<b>Votes: </b>".$row['votes']."<br/><br/>";

       

         
         $sql_question = sprintf("SELECT * FROM question WHERE refID='%s';", $id);
            $result_question = mysql_query($sql_question, $con);

            $question_counter = 1;

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
               
        if ($row['revenue']!= 0){

        print "<b>Revenue</b> in the next period will be ".$row['revenue']."(<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/><br/>";
   } // belongs to if ($revenue_option!= 0){

      
      if ($row['spending']!= 0){

    
        print "<b>Spending</b> in the next period will be ".$row['spending']."(<b>Note:</b> This value will not be displayed in the referendum. 
                    It will be used for calculating the result.)<br/><br/>";
   } 
}

print "</fieldset><div>";


print"<INPUT TYPE='button' VALUE='Back' class='btn btn-success' onClick='window.location=\"administration.php\";'>";

print "<div class='divider'></div>";

 $result_referendum = mysql_query($sql_referendum, $con);
 if ($row = mysql_fetch_assoc($result_referendum)) {

/* options depending on the type of referendum*/

/** draft or not started **/

     if (strcmp($row['status'], "draft")==0){

     	/* modify */
        print"<INPUT TYPE='button' VALUE='Create referendum' class='btn btn-success' onClick='window.location=\"successful_referendum.php?id=".$id."\";'>";
        print "<div class='divider'></div>";
        /* delete */
        
      /* primpts the user for confirmation before delete */
     print"<INPUT TYPE='button' VALUE='Delete' class='btn btn-success' onClick=\"javascript: deleteAlert('".$id."');\">";
    
        }


    else if(strcmp($row['status'], "not started")==0){
        /* primpts the user for confirmation before delete */
     print"<INPUT TYPE='button' VALUE='Delete' class='btn btn-success' onClick=\"javascript: deleteAlert('".$id."');\">";
     print "<div class='divider'></div>";


     print"<INPUT TYPE='button' VALUE='Start Referendum' class='btn btn-success' onClick='window.location=\"start_referendum.php?id=".$id."\";'>";
    }
    else if (strcmp($row['status'], "in progress")==0){
            /* calculate results */
         print"<INPUT TYPE='button' VALUE='Close Referendum' class='btn btn-success' onClick='window.location=\"stop_referendum.php?id=".$id."\";'>";
     }

    /* finished */
     else if (strcmp($row['status'], "finished")==0){
            /* calculate results */
         print"<INPUT TYPE='button' VALUE='Calculate Results' class='btn btn-success' onClick='window.location=\"calculate_result.php?id=".$id."\";'>";
     }
         
    /* calculated results */
     else if (strcmp($row['status'], "calculated_result")==0){
         /* publish */
         print"<INPUT TYPE='button' VALUE='Publish' class='btn btn-success' onClick='window.location=\"publish_result.php?id=".$id."\";'>";
        
        print"<INPUT TYPE='button' VALUE='View Result' class='btn btn-succcess' onClick='window.location=\"view_result.php?id=".$id."\";'>";
     }

     else if (strcmp($row['status'], "published")==0){
         /* view result */
        print"<INPUT TYPE='button' VALUE='View Result' class='btn btn-success' onClick='window.location=\"view_result.php?id=".$id."\";'>";
          
     }

     

     print "</div>";

}
mysql_close($con);














?>
</body>
</html>
