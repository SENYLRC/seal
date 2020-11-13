<?php
//This will marked if Illiad finished the request
#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);



//Get data about requests from database
$sqlselect = "SELECT *  FROM `SENYLRC-SEAL2-STATS` WHERE `IlliadStatus` LIKE '%Shipped%' or `IlliadStatus` LIKE '%Awaiting%'";
$retval = mysqli_query($db, $sqlselect);
$GETLISTCOUNT = mysqli_num_rows($retval);

while ($row = mysqli_fetch_assoc($retval)) {
    //Get data from Database
    $Illiadid      = $row["IlliadTransID"];
    $sqlidnumb = $row["index"];
    $reqnumb = $row['illNUB'];
    $destlib=$row['Destination'];
    $title = $row['Title'];
    $requesterEMAIL=$row['requesterEMAIL'];
    //Get data about Destination library from database
    $GETLISTSQLDEST="SELECT `APIkey`, `IlliadURL`, `Name`, `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$destlib'  limit 1";
    $resultdest=mysqli_query($db, $GETLISTSQLDEST);
    while ($rowdest = mysqli_fetch_assoc($resultdest)) {
        $destlib=$rowdest["Name"];
        $apikey=$rowdest["APIkey"];
        $illiadURL=$rowdest["IlliadURL"];
    }



    //set up email headers
    $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    //build the curl command
    $url =$illiadURL."Transaction/".$Illiadid."";
    $cmd = "curl -H ApiKey:".$apikey." ".$url."";
    //echo  "my cmd is ".$cmd."\n\n";
    $output = shell_exec($cmd);

    //decode the output from json
    $output_decoded = json_decode($output, true);
    $illiadtxnub= $output_decoded['TransactionNumber'];
    $status = $output_decoded['TransactionStatus'];
    $reasonCancel = $output_decoded['ReasonForCancellation'];
    $dueDate = $output_decoded['DueDate'];
    $dueDate = strstr($dueDate, 'T', true);
    //debuging output
    //echo "Trans Numb ".$illiadtxnub."\n";
    //echo "Status ".$status."\n";
    //echo "Cancel Reason ".$reasonCancel."\n";
    //echo "Due Date ".$dueDate."\n";


    //IF request was filled mark it as fill and send out email
    if (strpos($status, 'Request Finished') !== false) {
        // echo "item has been filled\n\n";
        $sqlupdate2 = "\n UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `checkinAccount`='ILLiad',`returnAccount` = 'ILLiad', `IlliadStatus` = '$status' WHERE `index` = $sqlidnumb\n";
        echo $sqlupdate2;
        //do database update and see if there was an error
        if (mysqli_query($db, $sqlupdate2)) {
            echo "database was updataed";
        //if error happen let tech support know
        } else {
            $to = "noc@senylrc.org";
            $message="SEAL was not able to update ILLiad status";
            $subject = "SEALL/ILLiad Database Update Failure  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f ill@senylrc.org");
        }//end check for database update
    }
}//end while loop of sql results
