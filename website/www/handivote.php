<?php

/* 
Voting page - voters access this page to submit their votes; 
page calles itself with the vote via POST request
receives SMS votes via REQUEST
validates the input and displays a thank you message

Author: Tsvetelina Valcheva
Last Modified: 09/03/13
*/


include('sanitise.php');
include('connection_db.php');
include('script.js');

/*
function to convert a GET request into a string representation of the request ;
checks if a GET request is set with [qr, qr_card, qr_poster]
if so, converts the GET request to a string that will be attached to to the page name whenever the page is reloaded
*/
function voting_method(){
  $method = "";

  if (isset($_GET['qr'])|| isset($_GET['qr_card'])){
    $method .= "?qr_card=1";
  }

  else if (isset($_GET['qr_poster'])){
    $method .= "?qr_poster=1";
  }
   
  else {
    $method .= "";
  }

  return $method;
}

/*
function to convert a GET request into a string ;
checks if a GET request is set with [qr, qr_card, qr_poster]
if so, converts the GET request to a string that will be inserted in the database together with the vote, 
as to indicate what voting method the voter used
*/

function string_voting_method(){
  $method = "";

  if (isset($_GET['qr_card'])){
    $method .= "qr_card";
  }

  else if (isset($_GET['qr_poster'])){
    $method .= "qr_poster";
  }
   
  else {
    $method .= "";
  }

  return $method;
}


/****** PROCESS THE VOTE ******/

/* global variables */
$GLOBALS['no_errors']= true; 
$GLOBALS['questionids']= array();
$GLOBALS['refid'] = "";
$GLOBALS['IP'] = "";


/** if the voter has voted by either SMS or through the interface **/
if ((isset($_REQUEST['barcode']) || isset($_POST['sumbit'])) && !isset($_GET['qr'])){  // (2)


  /** INPUT VALIDATION **/

  /* CHECK OPTIONS */

  /* get the referendum (we are assuming one referendum is 'in progress' at any time) */
  $sql_referendum    = "SELECT * FROM referendum WHERE `status` LIKE 'in progress'";
  $result_referendum = mysql_query($sql_referendum, $con);
  $row = mysql_fetch_assoc($result_referendum);
 
  $GLOBALS['refid'] = $row['id']; // store the referendum id in a global variable, as it will be used many times; this avoids multiple accesses to the database

  /* we need the question IDs for the current referendum because they are used in the names of the optins that are POST-ed */
  /* get the question ids */
  $question  = "SELECT * FROM question WHERE refID=" . $GLOBALS['refid'];
  $result_question = mysql_query($question, $con);

  $option_number = 1; // option_number always starts at 1; 

  while ($row = mysql_fetch_assoc($result_question)) {
    $Qid = $row['id'];
    $GLOBALS['questionids'][] = $Qid; // store question id's in a global variable, will be used multiple times

    /* find out if the correct number of options are received (checks for both votes via interface and SMS) */
    if (empty($_POST['question'.$Qid]) && empty($_REQUEST['option'.$option_number])){
        $GLOBALS['no_errors']= false;
    }
    $option_number++;
                                      
  }



  /* if no PIN is received */
  if (empty($_POST['pin']) && empty($_REQUEST['pin'])){
    $GLOBALS['no_errors']= false;
    }



  /** if no barcode is received **/
  if (empty($_POST['barcode']) && empty($_REQUEST['barcode'])){
    $GLOBALS['no_errors']= false;
  }


/** if input was not valid, message will be displayed; no further processing at this stage **/
/** if input was valid, the vote will be processed now **/
  if ($GLOBALS['no_errors']) {   // (1)

/** if the voter has used the web interface the barcode and pin will be coming via POST request  **/
    if (isset($_POST['submit'])) {
        $barcode = sanitize($_POST['barcode']);
        $pin     = sanitize($_POST['pin']);
     }

/** if the voter has used SMS the barcode and pin will be coming via REQUEST   **/
    else if (isset($_REQUEST['barcode'])){
        $barcode = sanitize($_REQUEST['barcode']);
        $pin = sanitize($_REQUEST['pin']);
    }

/* remove any white spaces from the barcode and pin */
    $barcode = str_replace(' ', '', $barcode);
    $pin = str_replace(' ', '', $pin);

/* if the voting is through SMS - we have no IP info, if through the web interface, we will keep the IP info */

    if (empty($_REQUEST['option1'])){
      $GLOBALS['IP'] = $_SERVER['REMOTE_ADDR'];
    }


 /** this connects to the webservie passing it the barcode and the pin (matric number)  **/
 /** returns 1 if valid combination **/
 /** returns 0 if invalid combination **/

    $result_verify = file_get_contents($verify_credentials."?barcode=" . $barcode . "&pin=" . $pin);
    //$result_verify = 1;
    /** get the current referendum - by requirements there will ever be only one referendum in progress at a time **/
    $sql_referendum    = "SELECT * FROM referendum WHERE `id` LIKE '".$GLOBALS['refid']."'";
    $result_referendum = mysql_query($sql_referendum, $con);

    while ($row = mysql_fetch_assoc($result_referendum)) {  //(9)

      /** make sure that the referendum is indeed live, by comparing start and end date to current time **/     
      $diffStartDate = strtotime("now") - strtotime($row['startDate']);
      $diffEndDate   = strtotime("now") - strtotime($row['endDate']);  
          
      if ($diffStartDate > 0 && $diffEndDate < 0) { //(8)
                       
        /***********************  GET ALL OPTIONS AND SANITISE THEM  ************/ 

        /** get the options - either by SMS or through the interface **/
                             
        $hidden_options = array();       
        $hidden_option = 0;
        $option_number = 1;
        
        foreach ($GLOBALS['questionids'] as $value){
                          
          if (isset($_REQUEST['option'.$option_number])){
            $hidden_option = sanitize($_REQUEST['option'.$option_number]); 
            $option_number++;
          }
          else{
            $hidden_option = sanitize($_POST['question'.$value]);
          }
               
          $hidden_options[] = $hidden_option;       
        } 
 

        /**************************** INVALID CREDENTIALS *********************/

        /** If the credentials are not valid, insert all votes in bad_vote table **/
        if ($result_verify == 0) {

          foreach ($hidden_options as $value){

            $insert_bad_vote = sprintf("INSERT INTO `handivote`.`bad_vote` (`id`, `refID`, `optID`, `barcode`, `ip`,`timestamp`, `type`, `method`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s');", $GLOBALS['refid'], $value, $barcode, $GLOBALS['IP'], gmdate("D M d, Y G:i a"), "failed_credentials", string_voting_method());
            mysql_query($insert_bad_vote, $con);
          }
          
          
          /* display thank you message and terminate */
          header("Location:thankyou.php");
          
          die();       
        }

        /************************ VALID CREDENTIALS ***************************************/
        /** if the credentials are valid continue processing **/
        if ($result_verify == 1) { //(7)

        /**********************************  DEAL WITH SMS INPUT   ************************************/
                     /* check if the voter sent SMS with non existent options */
                   
          if (!empty($_REQUEST['option1'])){ //(3)
                     
            /************************** NON EXISTENT OPTION *********************/
            $index = 0;
            $invalid = false;

            /* take all valid options from the db and put them in array */
            foreach ($GLOBALS['questionids'] as $value){
              $valid_options = array();

              $sql_valid_options = sprintf("SELECT * FROM `questionoption` WHERE `qID` like '%s' and `refID` like '%s' ", $value, $GLOBALS['refid']);
              $result_valid_options = mysql_query($sql_valid_options, $con);

              /* get all option numbers for this question */
              while ($row9 = mysql_fetch_assoc($result_valid_options)) {       
                $valid_options[] = $row9['optionnum']; 
              }

              /* add N to valid options, as it represents a spoiled ballot option from SMS */
              $valid_options[] = 'N';

              /* if the option submitted for this question is not among the option numbers of the question, we will invalidate the entire vote */
              if (!in_array($hidden_options[$index], $valid_options)){
                $invalid = true;                    
              }

              $index+=1;
            }

            /* insert vote into bad_vote */
            if ($invalid){
              foreach ($hidden_options as $value){
                $insert_bad_vote = sprintf("INSERT INTO `handivote`.`bad_vote` (`id`, `refID`, `optID`, `barcode`, `ip`,`timestamp`, `type`, `method`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s');", $GLOBALS['refid'], $value, $barcode, "", gmdate("D M d, Y G:i a"), "invalid_option_sms", string_voting_method());
                mysql_query($insert_bad_vote, $con);
              }
              
              /* display thank you message and terminate */
              header("Location:thankyou.php");
              die();
            }                        
                    
            /***************************** CONVERT FROM OPTION NUMBERS TO OPTION IDs **************************/

                   /* if the vote came through SMS then hidden_options contains option numbers (1 to 5) not options ids (all sorts of values) */
                   /* in the database a option number for a question corresponds to a unique option id */

                   /* before we continue we need to convert the SMS hidden_options from option numbers to option ids */
            $index = 0;
            foreach ($GLOBALS['questionids'] as $value){ 
                   
              $get_option_ids = sprintf("SELECT `id` FROM `questionoption` WHERE `refID` like '%s' and `qID` like '%s' and `optionnum` like '%s'", $GLOBALS['refid'], $value, $hidden_options[$index]);
              $resultOptionIDs = mysql_query($get_option_ids, $con);
                                       
              if ($row = mysql_fetch_assoc($resultOptionIDs)){                     
                $hidden_options[$index] = $row['id'];     
              }
              $index+=1;
            }
          } //(3)


          /**************************** ALL VOTES (INTERFACE AND SMS)  *************************/

          /************************************** REPEAT VOTE *****************************/

          /*** see if the barcode already in the DB - the voter has already voted   ***/
          $findBarcode = sprintf("SELECT * FROM vote WHERE barcode='%s' and `refID` like '%s' ", $barcode, $GLOBALS['refid']);
          $resultFindBarcode = mysql_query($findBarcode, $con);

          /** if we have more than one question the database will hold > 1 votes for same barcode since we are storing a separate vote for each question; store all options in an array **/ 
          if (mysql_num_rows($resultFindBarcode)>0) { //(5)
                      
            /* check if vote is not already voided */
            $row = mysql_fetch_assoc($resultFindBarcode);
            if ($row['void']==0){ //(4)
                $optionsFromDB = array();
                $resultFindBarcode = mysql_query($findBarcode, $con);
                
                while ($row4 = mysql_fetch_assoc($resultFindBarcode)) {
                    $optionsFromDB[] = $row4['optionID'];
                  }

                /***  for each of the new options: compare with option taken from database - if any of them is different set void to 1 for each vote from this barcode
                  (if all new options are the same as the old option i.e. the voter votes the same way than do not void the vote ) **/
                         
                $optionsFromDB[] = 'N';
                $void = false;
                foreach ($hidden_options as $value) {
                  if (!in_array($value, $optionsFromDB)) {
                    $void = true;                       
                  }    
                }

                if($void){
                  $updateVoid = sprintf("UPDATE `vote` SET `void`= '1' WHERE `barcode` LIKE '%s' and `refID` like '%s'", $barcode, $GLOBALS['refid']);
                  mysql_query($updateVoid, $con);
                  
                  /* update the vote count - we need to decrease the vote count now, because the vote is voided */
                  $select_votes = "SELECT `votes` FROM `referendum` WHERE `id` LIKE '".$GLOBALS['refid']."'";
                  $result_votes = mysql_query($select_votes, $con);
                  
                  if ($row = mysql_fetch_assoc($result_votes) ){
                    $votes = $row['votes'];
                    $votes = $votes - 1;   
                    $update_votes =  " UPDATE `referendum` SET `votes`= '".$votes."' WHERE `id` LIKE '".$GLOBALS['refid']."'";
                    $result_update = mysql_query($update_votes, $con);
                  }

                }
            } //(4)
            
            //insert entry in bad_vote for every repeat vote - regardless if user voted the same way or another way
            foreach ($hidden_options as $value) {
              $insert_bad_vote = sprintf("INSERT INTO `handivote`.`bad_vote` (`id`, `refID`, `optID`, `barcode`, `ip`,`timestamp`, `type`, `method`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s');", $GLOBALS['refid'], $value, $barcode, $GLOBALS['IP'], gmdate("D M d, Y G:i a"), "repeat_vote", string_voting_method());
              mysql_query($insert_bad_vote, $con);
            }
                           
          } //(5)

          /**************************************  INSERT NEW VOTE **********************************************/

          // we get here if the barcode is not in the database
          else { //(6)
            /* insert a vote entry for every question */
                         
            $index = 0;
            foreach ($GLOBALS['questionids'] as $value) {
                $insert = sprintf("INSERT INTO `handivote`.`vote` (`id`, `barcode`, `questionID`, `optionID`, `ip`,`timestamp`, `refID`, `method`) VALUES (NULL, '%s', '%s', '%s', '%s', '%s', '%s', '%s');", $barcode, $value, $hidden_options[$index], $GLOBALS['IP'], gmdate("D M d, Y G:i a"), $GLOBALS['refid'], string_voting_method() );
                 
                if (!mysql_query($insert, $con)) {die('Error: ' . mysql_error());}
                $index+=1;
            }

            /* update the vote count */

            $select_votes = "SELECT `votes` FROM `referendum` WHERE `id` LIKE '".$GLOBALS['refid']."'";
            $result_votes = mysql_query($select_votes, $con);
                         
            if ($row = mysql_fetch_assoc($result_votes) ){
                $votes = $row['votes'];
                $votes = $votes + 1;
                $update_votes =  " UPDATE `referendum` SET `votes`= '".$votes."' WHERE `id` LIKE '".$GLOBALS['refid']."'";
                $result_update = mysql_query($update_votes, $con);
            }
                              
          } // (6)
        } // (7)
      } // (8) 
    }  // (9)
         
  
  /** send the voter to the thank you page **/
  header("Location:thankyou.php");
 } //(1)
} //(2)

/******  HTML for the page *****/
/* the following style settings are adjusted for this page */
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- this line optimises screen for smart phones-->
<meta name="HandheldFriendly" content="true"/> <!-- this line optimises screen for feature phones-->
<title>HandiVote</title>

<?php

/* this include statements have to be here and not on top of the page, because the HTML inside interferes with the header() calls above */
include('css_files.php');

?>

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
width: 50em;
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

}

</style>
</head>
<body>
<?php

/** If the application has not received a SMS and the Submit button has not been pressed (the voter has not attempted to vote)
display the user interface **/

/** welcome message and image **/

print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";
print "</h2></div>";
 

/** get the current referendum **/
$sql_referendum    = "SELECT * FROM referendum WHERE `status` LIKE 'in progress'";
$result_referendum = mysql_query($sql_referendum, $con);

 while ($row = mysql_fetch_assoc($result_referendum)) {
        $id = $row['id'];
        
        $diffStartDate = strtotime("today") - strtotime($row['startDate']); // checks whether the referendum is live
        $diffEndDate   = strtotime("now") - strtotime($row['endDate']); // by comparing start and end date to current time 

        /* if no referendum running, dispay a message and terminate */
        if (!($diffStartDate >= 0 && $diffEndDate < 0)) {
            die("No referendums currenly running");
        }
        else {
            print "<h3>" . $row['title'] . "</h3>"; // prints the name of the referendum
            
            $sql_question    = "SELECT * FROM question WHERE refID=" . $id;
            $result_question = mysql_query($sql_question, $con);
          
            $question_number = 1;

            $var = voting_method(); // find out the voting method (qr, web, qr_poster)
 
            /* INPUT FORM */

             /************************ CONFIGURE HERE ****************/
            /* here uncomment or change depending on the server that is used to run the system - the path name will be different */
            print "<form name='authentication' method= 'post' action='/handivote/handivote.php".$var."' class='form-inline' onsubmit='return confirm(\"Did you fill in your Card Number and PIN correctly?\");'>";
           // print "<form name='authentication' method= 'post' action='/html/handivote.php".$var."' class='form-inline' onsubmit='return confirm(\"Did you fill in your Card Number and PIN correctly?\");'>";
            /** take each question for this referendum from the table "question" and print it out  **/
            while ($row2 = mysql_fetch_assoc($result_question)) {             
                $idQ = $row2['id'];
                print "<fieldset><div id = 'q" . strval($idQ) . "' onclick=\"expandElement('paragraph" . strval($idQ) . "');\"><b>Question " . $question_number . ". </b>" . $row2['question'] . " </div>"; 
                print "<div id='paragraph" . strval($idQ) . "' style='display: block;'>";
            
                /** get the options in a random order each time **/              
                $sql_option    = "SELECT * FROM questionoption WHERE qID=" . $idQ." ORDER BY RAND()";

                /* alternative query if the options need to appear in the order they are in the database */
                /*  $sql_option    = "SELECT * FROM questionoption WHERE qID=" . $idQ;  */

                $result_option = mysql_query($sql_option, $con);
                
                print " <select class='span3' id='select" . $idQ . "' name='question" . $idQ . "'>"; 
                print "<option value='' selected>Please select one...</option>";

                /** take each option for this question and print it out **/
                /** options displayed as a drop down menue **/
                $optionCounter = 1;
                while ($row3 = mysql_fetch_assoc($result_option)) {
                    print "<option value=" . $row3['id'] . "> " . $row3['option'] . "</option>";
                }
                print "</select>";
                print "</div>";
                
                /** displays the selection the voter made in red **/
                print "<div id='choice" . strval($idQ) . "' stype='display: none;'><b style = 'color:red' id='output" . $question_number . "'></b></div></fieldset>";
                $question_number = $question_number + 1;
             }
            
            /**  if the voter has already filled in the form but the form did not go through because some of the input was invalid, we already have the pin and barcode**/
           /** therefore take the pin and barcode and display them in the text boxes, saves the voter the time to type them again**/
            if (isset($_POST['barcode'])){
              $barcode = $_POST['barcode'];
            }
            else if (isset($_GET['barcode'])){
              $barcode = $_GET['barcode'];
            }

            else{ 
            $barcode = "";
            }
            
            if (isset($_POST['pin'])){
              $pin = $_POST['pin'];
            }

            else if (isset($_GET['pin'])){
              $pin = $_GET['pin'];
            }
            else{ 
            $pin = "";
            }
          
            /* MESSAGE - change from here if no longer relevant */
            print "<br/>Please use the Voting Card you have received from us to vote. The Card Number and PIN can be found on the card (see picture).<br/> Please keep your Voting Card safe, as you will need the Card Number to verify your vote was recorded correctly after the referendum is over.<br/>";
          

            /* CARD IMAGE - change from here if no longer relevant */
            print "<br/><input type='image' src='newcard.png' width='200' height='150'><br/>";
          
            //text boxes for pin and barcode
            print "<br/><label for='barcode'>Card Number:</label> <input type='text' name='barcode' id='barcode' value = '".$barcode."'/><br/><label for='pin'>PIN:</label> <input type='text' name='pin' id='pin' value = '".$pin."'/> ";

            /**hidden options are used to store the selected option value **/
            $sql_question    = "SELECT * FROM question WHERE refID=" . $id;
            $result_question = mysql_query($sql_question, $con);
            
            while ($row3 = mysql_fetch_assoc($result_question)) {
                print "<input type='hidden' value = '' name='hidden_option" . $row3['id'] . "' id='hidden_option" . $row3['id'] . "'/>";
             }
            
          
           /** if errors were found in the input, display this message **/
            if (!$GLOBALS['no_errors']){
              print "<br/><b style = 'color:red'>Please fill in all fields correctly. You need to select an option for all 3 questions above. </b>";
            }
          
            /** submit button **/
            print "</br></br><input type='submit' class='btn btn-success btn-large' name='submit' value = 'Submit Vote'/></form></br>";
         }
     }

mysql_close($con); // close the connection to the database
?> 
</body>
</html>

