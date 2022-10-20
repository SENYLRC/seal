<?php


#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$startdate = date('Y-m-d', strtotime('-7 days'));
$curdate = date('Y-m-d');
#use this to get stats from start of service
#$startdate  = "2016-07-01";
#Find the number of request created in the last seven days excluding SENYLRC
$GETREQUESTCOUNTSQL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' ";
$retval = mysqli_query($db, $GETREQUESTCOUNTSQL);
$row_cnt = mysqli_num_rows($retval);

#Find the number of request that were filled
$FINDFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' and Fill =1 ";
$retfilled =   mysqli_query($db, $FINDFILL);
$row_fill = mysqli_num_rows($retfilled);

#Find the number of request that were not filled
$FINDNOTFILL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' and Fill =0 ";
$retnotfilled =   mysqli_query($db, $FINDNOTFILL);
$row_notfill = mysqli_num_rows($retnotfilled);

#Find the number not answered yet
$FINDNOANSW= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' and Fill =3 ";
$retnoansw =   mysqli_query($db, $FINDNOANSW);
$row_noansw = mysqli_num_rows($retnoansw);

#Find the number of canceled
$FINDCANCEL= "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' and Fill =6 ";
$retcancel =   mysqli_query($db, $FINDCANCEL);
$row_cancel = mysqli_num_rows($retcancel);

#Find the number not answered yet
$FINDEXPIRE = "SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `Timestamp` > '$startdate 00:00:00' and `Requester LOC` != 'NHIGS' and Fill =4 ";
$retexpire =   mysqli_query($db, $FINDEXPIRE);
$row_expire = mysqli_num_rows($retexpire);

#Find the number of libraryies that are active
$actlib = "SELECT * FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant`=1 and `suspend`=0";
$retactlib =   mysqli_query($db, $actlib);
$row_actlib = mysqli_num_rows($retactlib);

#Find the number of libraryies that are suspended
$actlib3 = "SELECT * FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant`=1 and `suspend`=1";
$retactlib3 =   mysqli_query($db, $actlib3);
$row_actlib3 = mysqli_num_rows($retactlib3);


#Find the number of libraries who donot particpate and not suspended.  This should always be 0
$actlib2 = "SELECT * FROM `SENYLRC-SEAL2-Library-Data` WHERE `participant`=0 and `suspend`=0";
$retactlib2 =   mysqli_query($db, $actlib2);
$row_actlib2 = mysqli_num_rows($retactlib2);


######Message for SENYLRC Staff

$messagedest = "
SEAL stats from ". $startdate ." to  ".$curdate." <br>
These numbers excludes request placed by SENYLRC Staff<br><br>
       Request Filled: ".$row_fill."<br>
  Request Not Filled: ".$row_notfill."<br>
     Request Expired: ".$row_expire."<br>
Request not Answered: ".$row_noansw."<br>
    Request Canceled: ".$row_cancel."<br>
       Total Request: ".$row_cnt." <br>
<br><br>
          Number of Active Libraries: ".$row_actlib." <br>
Number of Active Libraries Suspended: ".$row_actlib3." <br>
<br>
(This should be always be zero)
Number of Libraries who do not participate and are not suspended ".$row_actlib2."<br>

<br>";

#######Set email subject for stats

$subject = "SEAL Stats for the week of ".$startdate." to ".$curdate."";

#####SEND EMAIL to SENYLRC ILL

$email_to = "ill@senylrc.org";
#$email_to = "spalding@senylrc.org";

 $headers = "From: SENYLRC SEAL <donotreply@senylrc.org>\r\n" ;
 $headers .= "MIME-Version: 1.0\r\n";
 $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

 mail($email_to, $subject, $messagedest, $headers);
        mail($email_to, $subject, $messagedest, $headers, "-f ill@senylrc.org");


?>
