<?php

/* 
Page that contains function to sanitise the input agianst sql injection; This page is included in all pages that take input from the user
I used this page for idea on how to implement the sanitize function:
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


/***  Function to sanitise SQL, removes white spaces and escapes special characters ***/
function sanitize($data){
  $data = trim($data);   
  $data = stripslashes($data);
  $data = mysql_real_escape_string($data);
  return $data;
}

?>