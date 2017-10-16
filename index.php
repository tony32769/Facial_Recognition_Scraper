<?php

include './includes/db.php';

$db = new DB();
echo "Attempting DB Connection<br>";
if(!$db){
	echo "there was a problem<br>";
	echo $db->error;
} else {
	echo "DB Connection successful";
}