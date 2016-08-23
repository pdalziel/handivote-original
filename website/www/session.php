<?php

/* 
Page that checks if the user is logged in - if the session variable Username is set; if the user is not logged in then the page redirects them to login.php
This page is included by all priviledged access administration pages
Author: Tsvetelina Valcheva
Last Modified: 28/01/13
*/


session_start();
if (!isset($_SESSION['username'])) {
header("location:login.php");
die();
}
?>