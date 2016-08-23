<?php
/* 
Connects to database and external service that verifies credentials
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


$con = mysql_connect("localhost", "root", "");
if (!$con) {
	die('Could not connect: ' . mysql_error());
}


/* connects to database "handivote"  */
mysql_select_db("handivote", $con);


/* this is the external service that verifies the credentials */
/* change this if the host of the service changes */
 $verify_credentials = "http://127.0.0.1:8500/verify.cfc";

 ?>
