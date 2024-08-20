<?php
include 'db_connect.php';

// Fetch the staff member's data based on the ID passed in the URL
$qry = $conn->query("SELECT * FROM staff_list WHERE id = ".$_GET['id'])->fetch_array();

// Loop through the fetched data and assign each field to a variable
foreach($qry as $k => $v){
	$$k = $v;
}

// Include the form or page that allows you to edit the staff details
include 'new_staff.php';
?>
