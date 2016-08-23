<?php

$returnval = 0;
/* Edit following line to your MySQL server details */
$db = new mysqli("HOSTNAME", "USERNAME", "PASSWORD", "cards");

if($db->connect_errno > 0){
    die('Unable to connect to database [' . $db->connect_error . ']');
}

$barcode = $_GET['barcode'];
$pin = $_GET['pin'];

$statement = $db->prepare("SELECT * FROM cards WHERE barcode=? and pin=?");
$statement->bind_param('ss', $barcode, $pin);
$statement->execute();
$statement->store_result();

$numberofrows = $statement->num_rows;

if($numberofrows > 0){
  $returnval = 1;
}

echo $returnval;

?>
