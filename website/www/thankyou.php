<?php
/* 
Displays a message informing user that their vote was successfully received; 
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/

?>


<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1" /> <!-- this line optimises screen for smart phones-->
<meta name="HandheldFriendly" content="true"/> <!-- this line optimises screen for feature phones-->
<title>HandiVote</title>
<?php
include('css_files.php');
/* the following style settings are adjusted for this page */
?>
<style type="text/css">     

body
{
  background-color: Snow;
  margin-top:5px;
  margin-left:1px;
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

</style>
</head>
<body>
<?php 

/* title */
print "<div class='page-header'><h2>Welcome to <input type='image' src='handivote.jpg' width='120' height='80'>";

/* message */
print "</h2></div>";
print "<div id='thankyou'>
    <h3><input type='image' src='smiley.jpg' width='60' height='40'>Thank you</h3><br/>
    <h4>You have successfully submitted your vote on ".gmdate("D M d, Y G:i a").".</h4><br/>
    <h4> Your IP Address is: ".$_SERVER['REMOTE_ADDR']." </h4><br/>
    <h4>Please <b style = 'color:red'>do not </b>attempt to vote again, as this will invalidate your first and any subsequent votes.<h4/><br/>

    <h4>Please come back to the HandiVote page to see the results and to verify your vote when the poll closes.</h4>
    </div>";
?>




</body>
</html>
