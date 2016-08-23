<?php

/* 
Calculates the result, applies vector optimisation to tax and spend votes
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


include('session.php');
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
  width:23em;
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

</style>
</head>
<body>

<?php

/* global variables, used for the calculation of the budget constrained result */
$GLOBALS['percentage_changes'] = array();
$GLOBALS['current_income_spending'] = array();
$GLOBALS['qIDs'] = array();
$GLOBALS['revenue_set'] = false;
$GLOBALS['spend_set'] = false;
$GLOBALS['average_changes'] = array();


/*
function to count the non voided votes from the database for a particular option 
parameters: optionID, database configuration information
returns: the number of votes
*/
function count_votes($optionid, $con){
  $countVotes = ''; 
  $sql_count = "SELECT COUNT(*) AS 'count' FROM `vote` WHERE `void` like '0' AND `optionID` like ".$optionid;
  $count = mysql_query($sql_count, $con);

  if ($row3 = mysql_fetch_assoc($count)){
      $countVotes = $row3['count'];
  }
  return $countVotes;
}

/*
function to calculate the inner product of two vectors of the same size 
parameters: two vectors
returns: the inner product of the two vectors
*/

function inner_product($vector1, $vector2){
  $ip = 0;
  for ($i = 0; $i<count($vector1); $i++){
     $ip += $vector1[$i]*$vector2[$i];
  }

  return $ip;
}



/* page title */
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";


/* page is expecting referendum ID coming via GET request - if there is no referendum ID then terminate with an error message */
if (empty($_GET['id'])){
  die("Access Denied");
}


  $id = sanitize($_GET['id']);

  /** get the referendum from the database **/
$sql = sprintf("SELECT * FROM `referendum` WHERE `id` LIKE '%s';", $id);
$result = mysql_query($sql, $con);

/* if there is no such referendum, terminate with an error message */
if (!$row = mysql_fetch_assoc($result)) {
  die("Access Denied");
}

else { /* (2) */

    /* checks whether the referendum is over, if it is not, terminate with an error message */
    if (strcmp($row['status'], "finished")!=0){
       die ("Access Denied");
     }


/******* CALCULATION RESULTS ******/

/** count the non-voided votes for every option for every question in the referendum and puts the count in the result table **/

 

/* take information from the referendum table on what the current revenue and spending are, these are not always supplied */
/* insert revenue in position 0 */
$revenue_change = '';
if (!empty($row['revenue'])){
  $GLOBALS['current_income_spending'][] = $row['revenue'];
  $revenue_change =$row['revenue_change']/100;
  $GLOBALS['percentage_changes'][] = $revenue_change;
  $GLOBALS['revenue_set'] = true;
}

/* insert spending in position either 0 or 1, depends on whether revenue was inserted */
$spend_change = '';
if (!empty($row['spending'])){
  $GLOBALS['current_income_spending'][] = -$row['spending'];
  $spend_change =$row['spending_change']/100;
  $GLOBALS['percentage_changes'][] = $spend_change;
  $GLOBALS['spend_set'] = true;
}

  /** get all the questions for this referendum **/
  $sql_question    = sprintf("SELECT * FROM `question` WHERE `refID` LIKE '%s';", $id );
  $result_question = mysql_query($sql_question, $con);

  /** for every question, find out what its type (simple, spend, tax) is and process accordingly **/
	while ($row1 = mysql_fetch_assoc($result_question)) { /* (1) */

		  $idQ = $row1['id'];

    /* spend and tax questions */
    if ($row1['type']=='tax' || $row1['type']=='spend'){
       $GLOBALS['qIDs'][] = $idQ;

       /** for every option in this question, find out the number of votes **/
       $total = 0;
       $total_number_votes = 0;
       $sql_option    = "SELECT * FROM questionoption WHERE qID=" . $idQ;
       $result_option = mysql_query($sql_option, $con);

       while ($row2 = mysql_fetch_assoc($result_option)) {
          $optionId = $row2['id'];
          $number_votes = count_votes($optionId, $con);
          $total_number_votes +=$number_votes;

          /* for every valid vote for this option add the percentage change that it represents - eg if option is 'increase by 5%' add 0.05 to total*/

          /* for spending our 'percent' values in the DB are the actual percentage changes, just need to divide them by 100 */
          $percent = 0;
          if ($row1['type']=='spend'){
            $percent = $row2['percent']/100;
          }

          /* for the tax the 'percent' in the database is the percentige value of the tax (e.g. VAT = 20%), thus we need to calculate the percentage change of the tax */
          else if($row1['type']=='tax'){
            $current_tax = $row1['current_tax'];
            $new_tax = $row2['percent'];
            $percent = abs(($new_tax-$current_tax)/$current_tax);

          }
          
         if($row2['value_option']==1){   // value_option = 1 represents an option to "increase" tax or spending
            $total +=$number_votes*$percent; // the value enters with positive sign in the total

          }
         else if($row2['value_option']==2){   //value_option = 2 represents an option to "decrease" tax or spending
            $total -=$number_votes*$percent;  // the value enters with negative sign in the total
          }

          }

          
          /* if we have no votes, the percentage change will be 0 */
          if ($total_number_votes==0){
            $GLOBALS['percentage_changes'][] = 0;
          }  

          /* if we have votes, enter the average of the votes */
          else{        
            $GLOBALS['percentage_changes'][] = $total/$total_number_votes;
          }

          if ($row1['type']=='tax'){
            $GLOBALS['current_income_spending'][] = $row1['current_tax_pound'];
          }
          else if ($row1['type']=='spend'){
            $GLOBALS['current_income_spending'][] = - $row1['current_spend'];
          }
       }
	} /* (1) */


 /* calculate realisable point */
      $realisable = array();

      $inner_product1 = inner_product($GLOBALS['percentage_changes'],$GLOBALS['current_income_spending']);
      $inner_product2 = inner_product($GLOBALS['current_income_spending'],$GLOBALS['current_income_spending']);


      if ($inner_product2 == 0){
        $scalar = 0;
      }
      else{
        $scalar = $inner_product1/$inner_product2;
      }

      for ($i = 0; $i<count($GLOBALS['percentage_changes']); $i++){
        $realisable[$i] = $GLOBALS['percentage_changes'][$i] - $scalar*$GLOBALS['current_income_spending'][$i];     
      }

      /* save the percentage changes in another array, as this one will be overridden */
      $GLOBALS['average_changes'] = $GLOBALS['percentage_changes'];

      /* CASE 1: if either revenue or spending is provided but not both */
      if ($GLOBALS['revenue_set'] XOR  $GLOBALS['spend_set']){
        $change = '';

        if ($GLOBALS['revenue_set']){
          $change = $revenue_change;
        }
        else{
           $change = $spend_change;
        }


        /* this while loop iterates until the revenue or spending value converges to the value supplied by the interface */
        $precision = 7;   // adjust precision of result from here
                          // $precision = 4 means that for example 0.99999 is rounded up to 1
                          // increasing the precision means the loop will iterate more times, 

        while(round($realisable[0],$precision) != round($change,$precision)){

          // insert in percentage_changes the realisable values, keeping the percentage_change of revenue the same 
          for ($i = 1; $i<count($GLOBALS['percentage_changes']); $i++){
            $GLOBALS['percentage_changes'][$i] = $realisable[$i];
          }

          //  find inner product and scalar again - we need to recalculate these after each iteration, because the values change

          $inner_product1 = inner_product($GLOBALS['percentage_changes'],$GLOBALS['current_income_spending']);
          $inner_product2 = inner_product($GLOBALS['current_income_spending'],$GLOBALS['current_income_spending']);

          if ($inner_product2 == 0){
            $scalar = 0;
          }
          else{
            $scalar = $inner_product1/$inner_product2;
          }

          // find realisable again - needs to be recalculated with new values

          for ($i = 0; $i<count($GLOBALS['percentage_changes']); $i++){
           $realisable[$i] = $GLOBALS['percentage_changes'][$i] - $scalar*$GLOBALS['current_income_spending'][$i]; 
          }
        }
      }



     /* CASE 2: if both revenue and spending are provided */
    else if ($GLOBALS['revenue_set'] AND $GLOBALS['spend_set']){

      $precision = 7; 
      while( (round($realisable[0],$precision) != round($revenue_change,$precision)) AND (round($realisable[1],$precision) != round($spend_change,$precision)) ) {

        // insert in percentage_changes the realisable values, keeping the percentage_change of revenue the same 
        for ($i = 2; $i<count($GLOBALS['percentage_changes']); $i++){
            $GLOBALS['percentage_changes'][$i] = $realisable[$i];
        }

        //  find inner product and scalar again 
        $inner_product1 = inner_product($GLOBALS['percentage_changes'],$GLOBALS['current_income_spending']);
        $inner_product2 = inner_product($GLOBALS['current_income_spending'],$GLOBALS['current_income_spending']);

        if ($inner_product2 == 0){
          $scalar = 0;
        }
        else{
          $scalar = $inner_product1/$inner_product2;
        }

        // find realisable again 
        for ($i = 0; $i<count($GLOBALS['percentage_changes']); $i++){
          $realisable[$i] = $GLOBALS['percentage_changes'][$i] - $scalar*$GLOBALS['current_income_spending'][$i]; 
        }
      }
    }



/* insert realisable values and average values in the database table question */
$j=0;

if ($GLOBALS['revenue_set'] XOR $GLOBALS['spend_set'] ){
  $j = 1;
}
else if ($GLOBALS['revenue_set'] AND $GLOBALS['spend_set']){
  $j = 2;
}

$i=$j;

foreach ( $GLOBALS['qIDs'] as $q_id){
  $update_realisable_avg = "UPDATE `question` SET `realisable`='".$realisable[$j]."', `average`='".$GLOBALS['average_changes'][$j]."' WHERE `id` like '".$q_id."'";
  $update = mysql_query($update_realisable_avg, $con);      
  $j++;
}


/* calculate degree difference and update it in the database table question */

$degree = 0;

for ($i = $i; $i<count($GLOBALS['average_changes']); $i++){
  $degree += pow(($GLOBALS['average_changes'][$i] - $realisable[$i]), 2);
}

$degree = sqrt($degree);
$update_degree = sprintf("UPDATE `referendum` SET `degree`='%s' WHERE `id` like '%s';", $degree, $id);
$update = mysql_query($update_degree, $con);



/******** INSERT RESULTS TO DATABASE TABLE RESULTS *****/

$sql_question = sprintf("SELECT * FROM `question` WHERE `refID` LIKE '%s';", $id);
$result_question = mysql_query($sql_question, $con);

while ($row1 = mysql_fetch_assoc($result_question)) {

      $idQ = $row1['id'];

  /** get the relevant options **/
      $sql_option    = "SELECT * FROM questionoption WHERE qID=" . $idQ;
      $result_option = mysql_query($sql_option, $con);

      /** for every option **/
      while ($row2 = mysql_fetch_assoc($result_option)) {

                $optionId = $row2['id'];

                /** count the non-voided votes for that option **/
                
                $number_votes = count_votes($optionId, $con);

                if ($number_votes != ''){
                   /** insert the count in the results table **/
                  $insert = sprintf("INSERT INTO `results` (`id`, `refID`, `qID`, `optionID`, `number_votes`, `winner`) VALUES (NULL, '%s', '%s', '%s', '%s', '0');", $id, $idQ, $optionId, $number_votes);
                  $result_insert = mysql_query($insert, $con);
                }
      }

 /** Simple question will have a winner **/
  if ($row1['type']=='simple'){
    
      /** now find the option with the highest number of votes **/
     $max = sprintf("SELECT optionID, number_votes FROM `results` WHERE `refID` like '%s' and number_votes = (SELECT MAX( number_votes ) FROM `results` where `refID` like '%s' );", $id, $id );
     $result_max = mysql_query($max, $con);

      /** update the "winner" column marking the winner with 1. If a tie than more than one winners will be entered **/
      while ($row4 = mysql_fetch_assoc($result_max)){
              $winner =  $row4['optionID'];
              $update_winner = "UPDATE `results` SET `winner`= '1' WHERE `optionID` LIKE ".$winner;
              $result_winner = mysql_query($update_winner, $con);
      }
  }

}


 /** update the status of the referendum to prevent from calculating the votes twice**/
 $update_ref = "UPDATE `referendum` SET `status`= 'calculated_result' WHERE `id` LIKE ".$id;
 $result_update = mysql_query($update_ref, $con); 
 if (!$result_update){
  die(mysql_error());
 }

/* success message */
print "<fieldset>";
print "<h4>Successful calculation of results</h4></br></br>";

} /* (2) */

print "</fieldset>";
// back button
print"<INPUT TYPE='button' class='btn btn-success' VALUE='Back to main page' onClick='window.location=\"administration.php\";'>";

mysql_close($con); // close connection to the database

?>
</body>
</html>
