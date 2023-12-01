<?php
//This will marked if Illiad finished the request
#####Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);



//Get data about requests from database
$sqlselect = "SELECT *  FROM `$sealSTAT` WHERE `IlliadStatus` LIKE '%Shipped%' or `IlliadStatus` LIKE '%Shipped%'";
echo $sqlselect."\n";
$retval = mysqli_query($db, $sqlselect);
$GETLISTCOUNT = mysqli_num_rows($retval);

while ($row = mysqli_fetch_assoc($retval)) {
    //Get data from Database
    $Illiadid      = $row["IlliadTransID"];
    $sqlidnumb = $row["index"];
    $reqnumb = $row['illNUB'];
    $destlib=$row['Destination'];
    $title = $row['Title'];
    $origDueDate = $row['DueDate'];
    $requesterEMAIL = $row['requesterEMAIL'];
    //Get data about Destination library from database
    $GETLISTSQLDEST="SELECT `APIkey`, `IlliadURL`, `Name`, `ill_email` FROM `$sealLIB` where loc like '$destlib'  limit 1";
    echo $GETLISTSQLDEST."\n";
    $resultdest=mysqli_query($db, $GETLISTSQLDEST);
    while ($rowdest = mysqli_fetch_assoc($resultdest)) {
        $destlib=$rowdest["Name"];
        $apikey=$rowdest["APIkey"];
        $illiadURL=$rowdest["IlliadURL"];
    }

    //Check if working with NewPaltz and remove eFrom from end of URL
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
    $dueDate = $output_decoded['DueDate'];
    $dueDate = strstr($dueDate, 'T', true);
    //debuging output
    echo "Trans Numb ".$illiadtxnub."\n";
    echo "Status ".$status."\n";
    //echo "Cancel Reason ".$reasonCancel."\n";
    //echo "Due Date ".$dueDate."\n";

    //comprare due date
    if ($origDueDate==$dueDate) {
        echo "no date change \n";
    } else {
        if (strlen($dueDate)>2) {
            //set up email headers
            $headers = "From: SEAL <donotreply@senylrc.org>\r\n" ;
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $to=$requesterEMAIL;
            // $to = "spalding@senylrc.org";
            $message="eFrom Request ".$reqnumb." ".$reqnumbRequest." from ".$destlib." has a new due date which is ".$dueDate."<br>";
            $message.="This is an automated message from the eForm ILL System. Responses to this email will be sent back to staff at Capital District Library Council. If you would like to contact the oth
er library in this ILL transaction";
            $subject = "Request ".$reqnumb." has a new due date  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
        }
    }
    //IF request was finished, mark that in database
    if (strpos($status, 'Item Shipped') !== false) {
        // echo "item has been finished\n\n";
        $sqlupdate2 = "UPDATE `$sealSTAT` SET  `DueDate` = '$dueDate' WHERE `index` = $sqlidnumb\n";
        echo $sqlupdate2;
        //do database update and see if there was an error
        if (mysqli_query($db, $sqlupdate2)) {
            echo "database was updataed";
        //if error happen let tech support know
        } else {
            //set up email headers
            $headers = "From: SEAL <donotreply@senylrc.org>\r\n" ;
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $to = "noc@senylrc.org";
            $message="eForm was not able to update ILLiad status";
            $subject = "eForm/ILLiad Database Update Failure  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
        }//end check for database update
    }//end if to mark it finished

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
        if (strpos($reasonCancel, 'Poor condition') !== false) {
            $reasontxt='Poor condition';
            $nofillreason="24";
        }
        if (empty($nofillreason)) {
            $nofillreason="0";
            $reasontxt="not specified";
        }

        $sqlupdate2 = "\n UPDATE `$sealSTAT` SET `reasonNotFilled` = '$nofillreason',  `Fill` = '0' , `IlliadStatus` = '$status' WHERE `index` = $sqlidnumb\n";
        echo $sqlupdate2;
        //do database update and see if there was an error
        if (mysqli_query($db, $sqlupdate2)) {
            echo "database was updataed";
        //if error happen let tech support know
        } else {
            $to = "noc@senylrc.org";
            $message="eForm was not able to update ILLiad status";
            $subject = "eForm/ILLiad Database Update Failure  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
        }//end check for database update


        $message = "Your ILL request $reqnumb for $title can not be filled by $destlib.<br>".
                "Reason request can not be filled: $reasontxt".
                "<br><br> <a href='http://seal.senylrc.org'>Would you like to try a different library</a>?";
        //set up email headers
        $headers = "From: SEAL <donotreply@senylrc.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        #######Setup php email headers
        $to=$requesterEMAIL;
        //$to = "spalding@senylrc.org";
        $subject = "ILL Request Not Filled ILL# $reqnumb  ";
        #####SEND requester an email to let them know the request will be filled
        $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
    }//end the cancel check
}//end while loop of sql results


//if a request is older than 8 months, mark is as assume complete
//Get data about requests from database
$sqlselect = "SELECT *  FROM `$sealSTAT` WHERE `Timestamp` < CURRENT_TIMESTAMP - INTERVAL 7 MONTH and `IlliadStatus` LIKE '%Shipped%'";
$retval = mysqli_query($db, $sqlselect);
$GETLISTCOUNT = mysqli_num_rows($retval);

while ($row = mysqli_fetch_assoc($retval)) {
    //Get data from Database
    $Illiadid      = $row["IlliadTransID"];
    $sqlidnumb = $row["index"];

    // echo "after six months assume transation is over"
    $sqlupdate2 = "UPDATE `$sealSTAT` SET  `IlliadStatus` = 'Assume Compelete after 7 months' WHERE `index` = $sqlidnumb\n";
    echo $sqlupdate2;
    //do database update and see if there was an error
    if (mysqli_query($db, $sqlupdate2)) {
        echo "database was updataed";
    //if error happen let tech support know
    } else {
        //set up email headers
        $headers = "From: SEAL <donotreply@senylrc.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $to = "noc@senylrc.org";
        $message="eForm was not able to update ILLiad status";
        $subject = "eForm/ILLiad Database Update Failure  ";
        #####SEND requester an email to let them know the request will be filled
        $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
    }//end check for database update
}//end while loop of sql results
