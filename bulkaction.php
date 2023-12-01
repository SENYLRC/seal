<?php

//actions codes
//1 cancel  (sends email)
//2 renew  (sends email)
//3 received
//4 fill  (sends email)
//5 not fill  (send email)
//6 check item back in

$illsystemhost = $_SERVER["SERVER_NAME"];
// Connect to database
require '/var/www/seal_script/seal_db.inc';
require '/var/www/seal_script/seal_function.php';

$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

$timestamp = date("Y-m-d H:i:s");
$todaydate = date("Y-m-d");

//only process if data was posted
if (isset($_POST['bulkaction'])) {
    //set action variable

    $action=$_POST['bulkaction'];
    //pull the array of illids
    $ids = array();
    foreach ((array) $_POST['check_list'] as $id) {
        echo "Processing ILL #".$id."<br>";
        // Get title and request email and destination library
        $sqlselect="select requesterEMAIL,Title,Destination from  `$sealSTAT` where illNUB='$id'  LIMIT 1 ";
        $result = mysqli_query($db, $sqlselect);
        $row = mysqli_fetch_array($result);
        $title =$row['Title'];
        $requesterEMAIL=$row['requesterEMAIL'];
        $destlib=$row['Destination'];

        // Get the Destination Name
        $GETLISTSQLDEST="SELECT`Name`, `ill_email` FROM  `$sealLIB`  where loc like '$destlib'  limit 1";
        $resultdest=mysqli_query($db, $GETLISTSQLDEST);
        while ($rowdest = mysqli_fetch_assoc($resultdest)) {
            $destlib=$rowdest["Name"];
            $destemail=$rowdest["ill_email"];
        }
        // In case the ill_email for the destination library is more than one, break it down to a comma for PHP mail
        $destemailarray = explode(';', $destemail);
        $destemail_to = implode(',', $destemailarray);

        if ($action==1) {
            $sqlupdate = "UPDATE `$sealSTAT` SET `Fill` =  '6' WHERE `illNUB` = '$id'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "ILL #".$id." has been canceled<br>";

                $headers = "From: Southeastern SEAL <donotreply@senylrc.org>\r\n" ;
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                // Setting up email notification
                $message = "ILL request $id for $title has been canceled ";

                // $to="spalding@senylrc.org";
                $subject = "ILL Request Canceled ILL# $id  ";
                // SEND request an email to let them know the request will be filled
                $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                // mail has been sent to meg at seal for development
                //$destemail_to="spalding@senylrc.org";
                mail($destemail_to, $subject, $message, $headers, "-f donotreply@senylrc.org");
            } else {
                echo "<div style='color: red;>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql
        } elseif ($action==2) {
            $sqlupdate = "UPDATE `$sealSTAT`  SET `renewTimeStamp` = '".$timestamp."', `renewAccountRequester` = '" .$wholename."' WHERE `illNUB` = '$id'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "ILL #".$id." rewnew request sent<br>";

                // Message for the destination library
                $messagedest = $field_your_institution." has requested a renewal for ILL# ".$id."<br>Title: ".$title."<br><br>
                <br>
                How do you wish to answer the renewal?  <a href='http://$illsystemhost/renew?num=$id&a=1' >Approved</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='http://$illsystemhost/renew?num=$id&a=2' >Deny</a>
                <Br>
                <hr style='width:200px;text-align:left;margin-left:0'>
                <br>  This is an automated message from the SEAL. Responses to this email will be sent back to Capital District Library Council staff. If you would like to contact the other library in this ILL transaction, email ".$reqemail.".";
                // Set email subject for renewal
                $subject = "SEAL Renew Request: from ".$field_your_institution." ILL# $id";
                $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
                // SEND EMAIL to Detestation Library
                $email_to = implode(',', $destemailarray);
                $headers = "From: Southeastern SEAL <donotreply@senylrc.org>\r\n" ;
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                // mail has been sent to meg at seal for development
                //$destemail_to="spalding@senylrc.org";
                mail($destemail_to, $subject, $messagedest, $headers, "-f donotreply@senylrc.org");
            } else {
                echo "<div style='color: red;>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql
        } elseif ($action==3) {
            $sqlupdate = "UPDATE `$sealSTAT` SET `receiveTimeStamp` = '$timestamp', `receiveAccount` = '" .$wholename."', `receiveDate` = '$todaydate' WHERE `illNUB` = '$id'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "ILL #".$id." has been marked received<br>";
            } else {
                echo "<div style='color: red;>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql
        } elseif ($action==4) {
            // this this is to retun an item
            $sqlupdate = "UPDATE `$sealSTAT` SET `returnTimeStamp` = '$timestamp',`returnMethod` = '$returnmethod',`returnNote` = '$returnnote', `returnAccount` = '" .$wholename."', `returnDate` = '$todaydate', `patronnote` = '' WHERE `illNUB` = '$id'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "The requests has been marked return   <br>";
            } else {
                echo "<div style='color: red;>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql
        } elseif ($action==5) {
            $sqlupdate = "UPDATE `$sealSTAT` SET `emailsent` = '1', `Fill` = '0'    WHERE `illNUB` = '$reqnumb'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "ILL #".$id." has been marked not filled<br>";
                $headers = "From: Southeastern SEAL <donotreply@senylrc.org>\r\n" ;
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                $message = "Your ILL request $id for $title can not be filled by $destlib.<br>".
                "<br><br>$respnote<br><br> <a href='http://".$illsystemhost."'>Would you like to try a different library</a>?";
                // Setup php email headers
                $to=$requesterEMAIL;
                $subject = "ILL Request Not Filled ILL# $id  ";
                // SEND requester an email to let them know the request will be filled
                $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                // mail has been sent to meg at seal for development
                //$to="spalding@senylrc.org";
                mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
            } else {
                echo "<div style='color: red;'>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql
        } elseif ($action==6) {
            $sqlupdate = "UPDATE `$sealSTAT` SET `checkinTimeStamp` = '$timestamp', `checkinAccount` = '" .$wholename."'  WHERE `illNUB` = '$reqnumb'";
            if (mysqli_query($db, $sqlupdate)) {
                echo "ILL #".$id." has been marked checked in<br>";
            } else {
                echo "<div style='color: red;'>Bulk System error, request was not updated</div><br>";
            }//end if checking mysql

        }else{
            echo "<div style='color: red;'>Unknown action, please report this error to system admin</div><br>";
            echo "<div style='color: red;'>Action was ".$action."</div><br>";        
        }//end if checking for actions

    }//end foreach loop
} else {
    echo "<div style='color: red;'>Please go back to your profile and submit a bulk action through the interface</div><br>";
}//end if check if action was posted
