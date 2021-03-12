<?php
#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

//Get data about requests from database
$sqlselect = "SELECT *  FROM `SENYLRC-SEAL2-STATS` WHERE `IlliadStatus` LIKE '%Awaiting%'";
$retval = mysqli_query($db, $sqlselect);
$GETLISTCOUNT = mysqli_num_rows($retval);

while ($row = mysqli_fetch_assoc($retval)) {
    //Get data from Database
    $Illiadid      = $row["IlliadTransID"];
    $sqlidnumb = $row["index"];
    $reqnumb = $row['illNUB'];
    $destlib=$row['Destination'];
    $title = $row['Title'];
    $sealFILL = $row['Fill'];
    $requesterEMAIL = $row['requesterEMAIL'];
    //Get data about Destination library from database
    $GETLISTSQLDEST="SELECT `APIkey`, `IlliadURL`, `Name`, `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$destlib'  limit 1";
    $resultdest=mysqli_query($db, $GETLISTSQLDEST);
    while ($rowdest = mysqli_fetch_assoc($resultdest)) {
        $destlib=$rowdest["Name"];
        $destemail=$rowdest["ILL Email"];
        $apikey=$rowdest["APIkey"];
        $illiadURL=$rowdest["IlliadURL"];
    }


    //set up email headers
    $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    //set up the dest emails in case if have multple address to work with
    $destemailarray = explode(';', $destemail);
    $destemail_to = implode(',', $destemailarray);
    echo "\n\n emails: ".$destemail_to ."\n\n";
     //Check if working with NewPaltz and remove SEAL from end of URL
     if (strpos($illiadURL, 'newpaltz.edu') !== false) {
     $illiadURL=substr($illiadURL, 0, -5);
     }

    //build the curl command
    $url =$illiadURL." ".$Illiadid."";
    $url = str_replace(' ', '', $url);
    $cmd = "curl -H ApiKey:".$apikey." ".$url."";
    echo  "my cmd is ".$cmd."\n\n";
    $output = shell_exec($cmd);

    //decode the output from json
    $output_decoded = json_decode($output, true);
    $illiadtxnub= $output_decoded['TransactionNumber'];
    $status = $output_decoded['TransactionStatus'];
    $reasonCancel = $output_decoded['ReasonForCancellation'];

    $articleURL = $output_decoded['ArticleExchangeUrl'];
    $articlePASS = $output_decoded['ArticleExchangePassword'];
    $dueDate = $output_decoded['DueDate'];
    $dueDate = strstr($dueDate, 'T', true);
    //debuging output
    //echo "Trans Numb ".$illiadtxnub."\n";
    //echo "Status ".$status."\n";
    //echo "Cancel Reason ".$reasonCancel."\n";
    //echo "Due Date ".$dueDate."\n";

    //IF request was filled mark it as fill and send out email
    if ((!empty($dueDate))&&(strpos($status, 'Shipped') !== false)) {
        //echo "item has been filled\n\n";
        $sqlupdate2 = "\n UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `shipMethod`='empire',`DueDate` = '$dueDate',  `Fill` = '1' , `IlliadStatus` = '$status' WHERE `index` = $sqlidnumb\n";
        //echo $sqlupdate2;
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
        $message = "Your ILL request $reqnumb for $title will be filled by $destlib <br>Due Date: $dueDate<br><br>Shipped via: empire<br> ".
                                     "<br><br>Please email <b>".$destemail_to."</b> for future communications regarding this request ";
        #######Setup php email headers
        $to=$requesterEMAIL;
        //$to = "spalding@senylrc.org";
        $subject = "ILL Request Filled ILL# $reqnumb  ";
        #####SEND requester an email to let them know the request will be filled
        $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail($to, $subject, $message, $headers, "-f ill@senylrc.org");
    }

    //IF request was filled via oclc
    if (($sealFILL=='3')&&(strpos($status, 'Awaiting Article Exchange Notification') !== false)) {
        //echo "item has been filled\n\n";
        $sqlupdate2 = "\n UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `shipMethod`='OCLC Article Exchange', `DueDate` = 'None', `Fill` = '1' , `IlliadStatus` = 'Request Finished' WHERE `index` = $sqlidnumb\n
";
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
        $message = "Your ILL request $reqnumb for $title will be filled by $destlib <br><br>Shipped via: OCLC Article Exchange<br><br>Access at the follwoing URL: ".$articleURL."<br> Password: ".$article
PASS."
".
                                     "<br><br>Please email <b>".$destemail_to."</b> for future communications regarding this request ";
        #######Setup php email headers
        $to=$requesterEMAIL;
        $subject = "ILL Request Filled ILL# $reqnumb  ";
        #####SEND requester an email to let them know the request will be filled
        $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail($to, $subject, $message, $headers, "-f ill@senylrc.org");
    }





    //if request has been canceled mark it and let library know
    if ((strpos($status, 'Cancelled') !== false)&&(!empty($reasonCancel))) {
        //echo "item has been canceled\n\n";
        if (strpos($reasonCancel, 'In use') !== false) {
            $reasontxt='In Use';
            $nofillreason="20";
        }
        if (strpos($reasonCancel, 'Lost') !== false) {
            $reasontxt='Lost';
            $nofillreason="21";
        }
        if (strpos($reasonCancel, 'non') !== false) {
            $reasontxt='Non-Circulating';
            $nofillreason="22";
        }
        if (strpos($reasonCancel, 'Not on shelf') !== false) {
            $reasontxt='Not on shelf';
            $nofillreason="23";
        }
        if (empty($nofillreason)) {
            $nofillreason="0";
            $reasontxt="not specified";
        }

        $sqlupdate2 = "\n UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `reasonNotFilled` = '$nofillreason',  `Fill` = '0' , `IlliadStatus` = '$status' WHERE `index` = $sqlidnumb\n";
        //echo $sqlupdate2;
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


        $message = "Your ILL request $reqnumb for $title can not be filled by $destlib.<br>".
                "Reason request can not be filled: $reasontxt".
                "<br><br> <a href='http://seal.senylrc.org'>Would you like to try a different library</a>?";
        #######Setup php email headers
        $to=$requesterEMAIL;
        //$to = "spalding@senylrc.org";
        $subject = "ILL Request Not Filled ILL# $reqnumb  ";
        #####SEND requester an email to let them know the request will be filled
        $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail($to, $subject, $message, $headers, "-f ill@senylrc.org");
    }

    //Reques1t has not been process yet so don't do anything
    if ((empty($dueDate))&&(empty($reasonCancel))) {
        echo "item has been not been filled yet\n\n";
    }
}//end while loop of sql results
