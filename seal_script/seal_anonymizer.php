<?php
// Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

$currentDate = new DateTime();

// Subtract 60 months  the current date
//SE Poly is 5 years from ILL date
$currentDate->sub(new DateInterval('P60M'));

// Format the result as a string
$ydate = $currentDate->format('Y-m-d');


$testsqlselect ="select `Timestamp` from `$sealSTAT` WHERE `Timestamp` < '$ydate'";
$sqlupdate = "UPDATE `$sealSTAT` SET  `Title` = '', `Author` = '', `pubdate` = '', `reqisbn` = '', `reqissn` = '', `itype` = '', `Call Number` = '', `article` = '', `needbydate` = '', `patronnote` = '', `DueDate` = '',  `reqNOTE` =  '' WHERE `Timestamp` < '$ydate'";

//for test
echo $testsqlselect."\n\n";
echo $sqlupdate."\n\n";
$result=mysqli_query($db, $sqlupdate);
if ($result === false) {
    // The query failed
    echo "Update failed: " . mysqli_error($db);
    // You can handle the failure in your preferred way (e.g., log the error, show an error message, etc.)
} else {
    // The query was successful
    echo "Update succeeded!";
    // You can perform additional actions after a successful query here.
}



?>
