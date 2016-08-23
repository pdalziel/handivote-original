<?php

/* 
Page to create a new referendum, contains a form to be filled in with the details of the new referendum
Author: Tsvetelina Valcheva
Last Modified: 19/03/13
*/


include('session.php');
include('connection_db.php');
include('sanitise.php');



/***************************** PROCESS NEW REFERENDUM DETAILS *********************/
$q1_type="";
$GLOBALS['refId'] = ""; 
$GLOBALS['current_tax_percent'] = "";


/* if the Submit button of the form has been pressed */
if (isset($_POST['create_ref_button'])) {

  /******************************************************* Assumptions ******************************************************************************/
/**  mandatory fields: title, start date, end date, question 1, question1_option 1, question1_option 2,  - at least 1 question with at least 2 options **/
/**  Current Tax and Current Spend are mandatory because they are needed for the budget constraint calculations **/


/********************* VALIDATE INPUT *******************************************/

/* check if title is empty */

    $message="";
    if (empty($_POST['ref_title'])){
      $message .= "<b style = 'color:red'>Please enter a title.</b>"; 
    }


 /* checks whether referendum with this title already exists and is not a draft. Only unique titles are allowed */
    $verify_unique_title   = sprintf("SELECT * FROM `referendum` WHERE `status` <> 'draft' and `title` LIKE '%s';", sanitize($_POST['ref_title']));
    $result_verify = mysql_query($verify_unique_title, $con);
 
    if (!$result_verify) {
      die('Verify Unique Title sql fail: ' . mysql_error());
    }

    if (mysql_num_rows($result_verify)>0){
      $message .= "<br/><b style = 'color:red'>Please enter a unique title. A referendum with this title already exists. </b>";
    } 


/* if a draft with this title exists, delete the draft
(the user is trying to modify the draft, therefore we need to delete the previous version of the draft) */

      $referendum   = sprintf("SELECT * FROM `referendum` WHERE `status` like 'draft' and `title` LIKE '%s';", sanitize($_POST['ref_title']));
      $result_ref = mysql_query($referendum, $con);
      if (!$result_ref) {die('Referendum sql fail: ' . mysql_error());}

      if ($row0 = mysql_fetch_assoc($result_ref)) {
            $GLOBALS['refId'] = $row0['id'];  // store referendum ID in a global variable because we are going to use it many times; avoids multiple database accesses
 
            $delete_referendum = "DELETE FROM `referendum` WHERE `id` LIKE '".$GLOBALS['refId']."'";
            $delete = mysql_query($delete_referendum, $con);

            $delete_questions = "DELETE FROM `question` WHERE `refID` like '".$GLOBALS['refId']."'";
            $delete = mysql_query($delete_questions);

            $delete_options = "DELETE FROM `questionoption` WHERE `refID` like '".$GLOBALS['refId']."'";
            $delete = mysql_query($delete_options);

      }

    
  /*  check whether start and end dates are empty */

    if (empty($_POST['s_day']) || empty($_POST['s_month']) || empty($_POST['s_year'])){
      $message .= "<br/><b style = 'color:red'>Please enter a valid start date.</b>"; 
    }
    if (empty($_POST['e_day']) || empty($_POST['e_month']) || empty($_POST['e_year'])){
      $message .= "<br/><b style = 'color:red'>Please enter a valid end date.</b>"; 
    }

  /* checks whether there is already a referendum scheduled for these days, or overlapping with the dates */
    $new_startdate = date('Y-m-d', strtotime(sanitize($_POST['s_year'])."-".sanitize($_POST['s_month'])."-".sanitize($_POST['s_day'])));
    $new_enddate = date('Y-m-d', strtotime(sanitize($_POST['e_year'])."-".sanitize($_POST['e_month'])."-".sanitize($_POST['e_day'])));

    $dates = "SELECT `startDate` , `endDate` FROM `referendum`";

    $result_dates = mysql_query($dates, $con);
    while ($row = mysql_fetch_assoc($result_dates)) {

             if ((strtotime($new_startdate)>=strtotime($row['startDate']) && strtotime($new_startdate)<=strtotime($row['endDate']))
              || (strtotime($new_enddate)>=strtotime($row['startDate']) && strtotime($new_enddate)<=strtotime($row['endDate']))
              || (strtotime($new_startdate)<=strtotime($row['startDate']) && strtotime($new_enddate)>=strtotime($row['endDate'])) ){
              $message .= "<br/><b style = 'color:red'>Please schedule the referendum with different dates. There is already a referendum scheduled for these dates or overlapping with these dates. </b>";  
             break;
             }
    }

    /* checks whether the start date is after the end date, and whether the start date is at or after the current date */
    if ((strtotime($new_startdate)>=strtotime($new_enddate)) || (strtotime($new_startdate)<strtotime("today") ) ){
      $message .= "<br/><b style = 'color:red'>Please enter a valid date. </b>";
    }

    /* checks for at least 1 question */
    if (empty($_POST['question_type_q1'])){
      $message .= "<br/><b style = 'color:red'>Please enter at least 1 question.</b>"; 
    }

   
      // if it is a simple question - check the options for simple question, we require at least 2 options
       if (sanitize($_POST['question_type_q1'])==1){
        $GLOBALS['q1_type'] = "simple";
          if (empty($_POST['simple_option1_q1']) || empty($_POST['simple_option2_q1'])) {
             $message .= "<br/><b style = 'color:red'>Please enter at least 2 options.</b>";  }
          if (empty($_POST['text_simple_q1']) ){
             $message .= "<br/><b style = 'color:red'>Please enter a question description.</b>";}
       }
       // if it is a spending question - check the options for spending question, at least 2 options needed


        if (sanitize($_POST['question_type_q1'])==2){
          $GLOBALS['q1_type'] = "spend";
          if (empty($_POST['spend_option1_q1']) || empty($_POST['spend_option2_q1'])) {
             $message .= "<br/><b style = 'color:red'>Please enter at least 2 options.</b>";  }
          if (empty($_POST['text_spend_q1']) ){
             $message .= "<br/><b style = 'color:red'>Please enter a question description.</b>";}
       }

       // if it is a tax question - check the options for tax question, at least 2 options needed

        if (sanitize($_POST['question_type_q1'])==3){
          $GLOBALS['q1_type'] = "tax";
          if (empty($_POST['tax_option1_q1']) || empty($_POST['tax_option2_q1'])) {
             $message .= "<br/><b style = 'color:red'>Please enter at least 2 options.</b>";  }
          if (empty($_POST['text_tax_q1']) ){
             $message .= "<br/><b style = 'color:red'>Please enter a question description.</b>";}
       }
       


       /* if there were any error in the input, print all of them now */

    if ($message!=""){
      print $message;
    } 


/********************************  PROCESS INPUT  ****************************/
/* Valid Input, referendum with this title is ether a draft, or there is no such referendum*/
    else {


   /* get the new input */


     $title = sanitize($_POST['ref_title']);

     $sday = sanitize($_POST['s_day']);
     $smonth = sanitize($_POST['s_month']);
     $syear = sanitize($_POST['s_year']);
    
     $eday = sanitize($_POST['e_day']);
     $emonth = sanitize($_POST['e_month']);
     $eyear = sanitize($_POST['e_year']);

     $startdate = date('Y-m-d', strtotime($syear."-".$smonth."-".$sday));
     $enddate = date('Y-m-d', strtotime($eyear."-".$emonth."-".$eday));

     
     $revenue = sanitize($_POST['revenue']);
     $spending = sanitize($_POST['spending']);
     $revenue_change = sanitize($_POST['revenue_change']);
     $spending_change = sanitize($_POST['spending_change']);


/* create the new referendum - it is going to have the same ID as the previous draft; If there wasnt a previous draft, ID is "" and will be created by the database by incrementing the previous ID */

 $create_referendum    = sprintf("INSERT INTO `referendum` ( `id` , `title` , `startDate` , `endDate` , `votes` , `status` , `revenue`, `spending`, `revenue_change`, `spending_change`) 
VALUES ('%s' , '%s', '%s' , '%s', '%s', '%s', '%s','%s', '%s', '%s') ", $GLOBALS['refId'], $title, $startdate, $enddate, '0', 'draft', $revenue, $spending, $revenue_change, $spending_change);


$result = mysql_query($create_referendum, $con);

if (!$result) {die('Invalid query: ' . mysql_error());}


 $get_new_referendum    = sprintf("SELECT * FROM `referendum` WHERE `title` LIKE '%s';",$title );
 $result_referendum = mysql_query($get_new_referendum, $con);

  if (!$result_referendum) {die('Get new referendum sql ' . mysql_error());}

  while ($row = mysql_fetch_assoc($result_referendum)) { //(1)
    
        $GLOBALS['refId'] = $row['id']; // again retain referendum ID as global variable

        $question_number = 1; // we have at least 1 question

        while ($question_number<=5){  // we can have up to 5 questions as input from the form (by requirements)

        /* for each question, find out what was its type (simple, spend or tax) */
        $q_type = "";

       if (sanitize($_POST['question_type_q'.$question_number])==1){ $q_type="simple";}
       if (sanitize($_POST['question_type_q'.$question_number])==2){ $q_type="spend";}
       if (sanitize($_POST['question_type_q'.$question_number])==3){ $q_type="tax";}

          if (!empty($_POST['text_'.$q_type.'_q'.$question_number])){
 
        /* get all the information that might have been supplied for the question
        if any of the fields were not filled in, then they will remain empty in the database
        */
        $current_spend = sanitize($_POST['current_spend_q'.$question_number]); 
        $GLOBALS['current_tax_percent'] = sanitize($_POST['current_tax_q'.$question_number]); 
        $current_tax_pound = sanitize($_POST['current_tax_pound'.$question_number]); 
        $text_question = sanitize($_POST['text_'.$q_type.'_q'.$question_number]);
      
        /* insert into the database the new question */      
        $insert_question = sprintf("INSERT INTO `question` (`id`, `refID`, `question`, `type`, `current_spend`, `current_tax`, `current_tax_pound`) 
          VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s');", $GLOBALS['refId'], $text_question, $q_type, $current_spend, $GLOBALS['current_tax_percent'], $current_tax_pound);

        $result_insert = mysql_query($insert_question, $con);
         if (!$result_insert) {die('insert question: ' . mysql_error());}
         
         
          }
          $question_number+=1;
        }
         
         

} // (1)


/* now insert all options to the database */

/* first retrieve from the database the newly inserted questions */

$get_new_question   = "SELECT * FROM question WHERE refID=" . $GLOBALS['refId'];
$result_question = mysql_query($get_new_question, $con);
$q_number = 1;
 while ($row2 = mysql_fetch_assoc($result_question)) {             
    $idQ = $row2['id'];
    $type =  $row2['type'];

    $option_number = 1;
    
    /* if the question is simple */
    if (strcmp($type, 'simple')==0){
    while ($option_number<=5 && !empty($_POST[$type.'_option'.$option_number.'_q'.$q_number])){   // we have up to 5 options for each question

    /* get the text of the option */
     $text_option = sanitize($_POST[$type.'_option'.$option_number.'_q'.$q_number]);
    /* and insert it in the database */
    $insert_option = sprintf("INSERT INTO `questionoption` (`id`, `refID`, `qID`, `optionnum`,`option`) VALUES (NULL, '%s', '%s', '%s', '%s');",$GLOBALS['refId'], $idQ, $option_number,$text_option );


    $result_insert_option = mysql_query($insert_option, $con);
         if (!$result_insert_option) {die('result insert option: ' . mysql_error());}
    $option_number+=1;

    }
}

/* if the question is spend or tax */

   if (strcmp($type, 'spend')==0 || strcmp($type, 'tax')==0){
    while ($option_number<=5 && !empty($_POST[$type.'_option'.$option_number.'_q'.$q_number])){

/* get all information supplied for this option */
     $value_option = sanitize($_POST[$type.'_option'.$option_number.'_q'.$q_number]);
     $percent = sanitize($_POST[$type.'_option'.$option_number.'_q'.$q_number.'_percent']); 
     $text_option='';
/* find out if the option is to increase/decrease or leave as is */
     if ($value_option==1){

         if ($type == 'spend'){
              $text_option = 'increase by '.$percent.'%';
            }
        else if ($type == 'tax'){
               $text_option = 'increase to '.$percent.'%';
             }
     }
     if ($value_option==2){
        if ($type == 'spend'){
           $text_option = 'decrease by '.$percent.'%';
         }
        else if ($type == 'tax'){
          $text_option = 'decrease to '.$percent.'%';
        }

     }
     if ($value_option==3){
      $text_option = 'leave as is';
      $percent = 0;
     }

    
/* insert the option to the database */
    $insert_option = sprintf("INSERT INTO `questionoption` (`id`, `refID`, `qID`, `option`, `value_option`, `percent`) 
      VALUES (NULL, '%s', '%s', '%s', '%s', '%s');", $GLOBALS['refId'], $idQ, $text_option, $value_option, $percent);
    

    $result_insert_option = mysql_query($insert_option, $con);
         if (!$result_insert_option) {die('result insert option 2: ' . mysql_error());}
    $option_number+=1;

    }
}





    $q_number+=1;
  }


mysql_close($con); // close the connection to the database

/* everything is inserted to the database, so send the user to verify_referendum.php to verify the input
the referendum ID is passed to verify_referendum.php via GET
*/
      $id = $GLOBALS['refId'];
      $url = "verify_referendum.php?id=$id";
     header("Location:$url");
    }

        
       
     }
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
  margin-left:70px;
}

fieldset
{
border:1px solid white;
width:45em;
margin-top: 5px;
margin-bottom: 5px;
font-size: 120%;
background-color: Beige;
padding: 10px;
}

div
{
  width:60em;
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
<body><?php

/**  Define number of questions **/

$max_number_questions=5;

/**  Define number of options per question **/

$max_number_options=5;

/* title */

print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";
print "<div><p><b>Useful Tips:</b></p><p>1. Please note that <b>only 1 referendum can be live at any time </b>. Please schedule any new referendums after the end date of the previous one.</p>";
print "<p>2. If you <b>change the name</b> of a referendum, you will have to <b>delete the draft</b> that you have created from the main page dashboard before you can save your changes.</p>";
print "<p>3. If the <b>web form was refreshed due to input errors</b>, simply select the type of question you had before and the input you provided will be displayed. For example if you had selected Question 1 as a Simple question, just select it again and the question and options you have entered will be displayed.</p></div>";

/* input form for the new referendum 
starts with referendum title box
*/

 

if (isset($_POST['ref_title'])){
              $ref_title = $_POST['ref_title'];
            }
            
            else{ 
            $ref_title = "";
            }


print "
<div>
<form id='ref_form' name='ref_form' method='post' action='" . htmlentities($_SERVER['PHP_SELF']) . "'>
<legend>New Referendum</legend>

<label>Title:*</label>
<input type='text' name='ref_title' id='ref_title' value = '".$ref_title."' required/><br/><br/>
";

/* drop down menus for day, month and year for the start and end date */

print "
<label>Start Date:*</label>

<select name='s_day' id='s_day' required>
  <option value ='' selected>Date</option>
  <option value ='1'>1</option>
  <option value ='02'>2</option>
  <option value ='03'>3</option>
  <option value ='04'>4</option>
  <option value ='05'>5</option>
  <option value ='06'>6</option>
  <option value ='07'>7</option>
  <option value ='08'>8</option>
  <option value ='09'>9</option>
  <option value ='10'>10</option>
  <option value ='11'>11</option>
  <option value ='12'>12</option>
  <option value ='13'>13</option>
  <option value ='14'>14</option>
  <option value ='15'>15</option>
  <option value ='16'>16</option>
  <option value ='17'>17</option>
  <option value ='18'>18</option>
  <option value ='19'>19</option>
  <option value ='20'>20</option>
  <option value ='21'>21</option>
  <option value ='22'>22</option>
  <option value ='23'>23</option>
  <option value ='24'>24</option>
  <option value ='25'>25</option>
  <option value ='26'>26</option>
  <option value ='27'>27</option>
  <option value ='28'>28</option>
  <option value ='29'>29</option>
  <option value ='30'>30</option>
  <option value ='31'>31</option>
</select>

<select name='s_month' id='s_month' required>
  <option value ='' selected>Month</option>
  <option value ='01'>1</option>
  <option value ='02'>2</option>
  <option value ='03'>3</option>
  <option value ='04'>4</option>
  <option value ='05'>5</option>
  <option value ='06'>6</option>
  <option value ='07'>7</option>
  <option value ='08'>8</option>
  <option value ='09'>9</option>
  <option value ='10'>10</option>
  <option value ='11'>11</option>
  <option value ='12'>12</option>
  </select>

<select name='s_year' id='s_year' required>
  <option value ='' selected>Year</option>
  <option value ='2013'>2013</option>
  <option value ='2014'>2014</option>
  <option value ='2015'>2015</option>
  <option value ='2016'>2016</option>
  <option value ='2017'>2017</option>
  </select>

";

print "<br/><br/>
<label>End Date:*</label>

<select name='e_day' id='e_day' required>
  <option value ='' selected>Date</option>
  <option value ='01'>1</option>
  <option value ='02'>2</option>
  <option value ='03'>3</option>
  <option value ='04'>4</option>
  <option value ='05'>5</option>
  <option value ='06'>6</option>
  <option value ='07'>7</option>
  <option value ='08'>8</option>
  <option value ='09'>9</option>
  <option value ='10'>10</option>
  <option value ='11'>11</option>
  <option value ='12'>12</option>
  <option value ='13'>13</option>
  <option value ='14'>14</option>
  <option value ='15'>15</option>
  <option value ='16'>16</option>
  <option value ='17'>17</option>
  <option value ='18'>18</option>
  <option value ='19'>19</option>
  <option value ='20'>20</option>
  <option value ='21'>21</option>
  <option value ='22'>22</option>
  <option value ='23'>23</option>
  <option value ='24'>24</option>
  <option value ='25'>25</option>
  <option value ='26'>26</option>
  <option value ='27'>27</option>
  <option value ='28'>28</option>
  <option value ='29'>29</option>
  <option value ='30'>30</option>
  <option value ='31'>31</option>
</select>

<select name='e_month' id='e_month' required>
  <option value ='' selected>Month</option>
  <option value ='01'>1</option>
  <option value ='02'>2</option>
  <option value ='03'>3</option>
  <option value ='04'>4</option>
  <option value ='05'>5</option>
  <option value ='06'>6</option>
  <option value ='07'>7</option>
  <option value ='08'>8</option>
  <option value ='09'>9</option>
  <option value ='10'>10</option>
  <option value ='11'>11</option>
  <option value ='12'>12</option>
  </select>

<select name='e_year' id='e_year' required>
  <option value ='' selected>Year</option>
  <option value ='2013'>2013</option>
  <option value ='2014'>2014</option>
  <option value ='2015'>2015</option>
  <option value ='2016'>2016</option>
  <option value ='2017'>2017</option>
  </select><br/>

";

/* text boxes for questions
the first question is indicated as mandatory
next to each question there is a drop down menue, allowing user to indicate if the question is going to be spend, tax or simple
*/
/* text boxes for options depend on the type of question */
$counter_question=1;
$asterisk="";
$required="";
while ($counter_question<= $GLOBALS['max_number_questions']){
  if ($counter_question==1){ $asterisk="*";$required="required";}
  else {$asterisk="";$required="";}

if (isset($_POST['current_spend_q'.$counter_question])){
              $spend_value = $_POST['current_spend_q'.$counter_question];
            }
            
            else{ 
            $spend_value = "";
            }

if (isset($_POST['text_spend_q'.$counter_question])){
              $spend_text = $_POST['text_spend_q'.$counter_question];
            }
            
            else{ 
            $spend_text = "";
            }
  print "<div id='question".$counter_question."'><fieldset>
<label for='question".$counter_question."'><b>Question ".$counter_question.$asterisk."</b></label>
What kind of question is this going to be?
<select name='question_type_q".$counter_question."' id='question_type_q".$counter_question."'  ".$required.">
  <option value='' selected>Please select one...</option>
  <option value='1'>simple question</option>
  <option value='2'>spending question</option>
  <option value='3'>tax question</option>
</select>
<input type='button' class='btn btn-success' value='Select' onclick = \"displayOption('q".$counter_question."');\"/></fieldset>

<br/>
</div>


<div id='spend_options_q".$counter_question."' style='display: none'>
<label>Please enter your question:*</label>
<input class='input-xxlarge' type='text' name='text_spend_q".$counter_question."' id='text_spend_q".$counter_question."' value = '".$spend_text."'/><br/>

<label>Current Spend in Pounds:*</label>
<input type='text' name='current_spend_q".$counter_question."' id='current_spend_q".$counter_question."' value = '".$spend_value."'/><br/>";

/* spend question and options */

$counter = 1;
while ($counter<= $GLOBALS['max_number_options']){
  if ($counter_question==1 && ($counter==1 || $counter==2)){ $asterisk="*";}
  else {$asterisk="";}




	print "<label>Option".$counter.$asterisk."</label>
<select name='spend_option".$counter."_q".$counter_question."' id='spend_option".$counter."_q".$counter_question."'>
<option value='' selected>Please select one...</option>
  <option value='1'>increase by</option>
  <option value='2'>decrease by</option>
  <option value='3'>leave as it is</option>
</select>

<input type='text' name='spend_option".$counter."_q".$counter_question."_percent' size='3'>
%

<br/>";

$counter+=1;
}


/* simple question and options */
if (isset($_POST['text_simple_q'.$counter_question])){
              $simple_text = $_POST['text_simple_q'.$counter_question];
            }
            
            else{ 
            $simple_text = "";
            }

print "</div>
<div id='simple_options_q".$counter_question."' style='display: none'>
<label>Please enter your question:*</label>
<input class='input-xxlarge' type='text' name='text_simple_q".$counter_question."' id='text_simple_q".$counter_question."' value = '". $simple_text."'/><br/>";

$counter=1;
while ($counter<=$GLOBALS['max_number_options']){
  if ($counter_question==1 && ($counter==1 || $counter==2)){ $asterisk="*";}
  else {$asterisk="";}

if (isset($_POST['simple_option'.$counter.'_q'.$counter_question])){
              $value = $_POST['simple_option'.$counter.'_q'.$counter_question];
            }
            
            else{ 
            $value = "";
            }


print"<label>Option ".$counter.$asterisk."</label>
<input type='text' name='simple_option".$counter."_q".$counter_question."' id='simple_option".$counter."_q".$counter_question."' value = '".$value."'/><br/>";


$counter+=1;
}

/* tax question and options */

if (isset($_POST['text_tax_q'.$counter_question])){
              $tax_text = $_POST['text_tax_q'.$counter_question];
            }
            
            else{ 
            $tax_text = "";
            }

if (isset($_POST['current_tax_q'.$counter_question])){
              $tax_current = $_POST['current_tax_q'.$counter_question];
            }
            
            else{ 
            $tax_current = "";
            }
if (isset($_POST['current_tax_pound'.$counter_question])){
              $tax_pound = $_POST['current_tax_pound'.$counter_question];
            }
            
            else{ 
            $tax_pound = "";
            }

print "
</div>

<div id='tax_options_q".$counter_question."' style='display: none'>
<label>Please enter your question:*</label>
<input class='input-xxlarge' type='text' name='text_tax_q".$counter_question."' id='text_tax_q".$counter_question."' value = '".$tax_text."'/><br/>

<label>Current Tax:*</label>
<input type='text' name='current_tax_q".$counter_question."' id='current_tax_q".$counter_question."' value = '".$tax_current."'/>%<br/>

<label>Current Income from Tax in Pounds:*</label>
<input type='text' name='current_tax_pound".$counter_question."' id='current_tax_pound".$counter_question."' value = '".$tax_pound."'/><br/>

";


$counter=1;
while ($counter<=$GLOBALS['max_number_options']){
   if ($counter_question==1 && ($counter==1 || $counter==2)){ $asterisk="*";}
  else {$asterisk="";}

  print "<label>Option ".$counter.$asterisk."</label>
<select name='tax_option".$counter."_q".$counter_question."' id='tax_option".$counter."_q".$counter_question."'>
<option value='' selected>Please select one...</option>
  <option value='1'>increase to</option>
  <option value='2'>decrease to</option>
  <option value='3'>leave as it is</option>
</select>

<input type='text' name='tax_option".$counter."_q".$counter_question."_percent' size='3'>
%
<br/>";

  $counter+=1;
}

print"</div>";



$counter_question+=1;
}


/* general questions at the end of the form */

if (isset($_POST['revenue'])){
              $rev = $_POST['revenue'];
            }
            
            else{ 
            $rev = "";
            }

if (isset($_POST['revenue_change'])){
              $rev_ch = $_POST['revenue_change'];
            }
            
            else{ 
            $rev_ch = "";
            }            
if (isset($_POST['spending'])){
              $spen = $_POST['spending'];
            }
            
            else{ 
            $spen = "";
            }            

if (isset($_POST['spending_change'])){
              $spen_ch = $_POST['spending_change'];
            }
            
            else{ 
            $spen_ch = "";
            }  
print"
<fieldset>
<label><b>How much is your Revenue be in the current period?:</b></label>
<input type='text' name='revenue' id='revenue' value = '".$rev."'/>
</fieldset><br/>";

print"
<fieldset>
<label><b>By how much will your Revenue change in the next period?:</b></label>
<input type='text' name='revenue_change' id='revenue_change' value = '".$rev_ch."'/>%
</fieldset><br/>";

print"
<fieldset>
<label><b>How much will your Spending be in the current period?:</b></label>
<input type='text' name='spending' id='spending' value = '".$spen."'/>
</fieldset><br/>";

print"
<fieldset>
<label><b>By how much will your Spending change in the next period?:</b></label>
<input type='text' name='spending_change' id='spending_change' value = '".$spen_ch."'/>%
</fieldset><br/>";

 /** back button - takes the user to administration.php **/
print"</br></br><INPUT TYPE='button' VALUE='Back' class='btn btn-success' onClick='window.location=\"administration.php\";'>";
print "<div class='divider'></div>";

/* create referendum button, sends the input via POST */
print "<input type='submit' name='create_ref_button' class='btn btn-success' id='create_ref_button' value='Create Referendum' />";

print "<br/><br/>
</form>
</div>";




?></body>
</html>