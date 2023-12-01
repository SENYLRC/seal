<?php

// Get values
$reqnumb=$_REQUEST["num"];
if (isset($_REQUEST['a'])) {
    $reqanswer = $_REQUEST['a'];
} else {
    $reqanswer='';
}


// Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

// Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$reqanswer = mysqli_real_escape_string($db, $reqanswer);


// Process any notes from the lender#############################
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $respnote=$_REQUEST["respondnote"];
    $resfill=$_REQUEST["fill"];

    //get title value before removal and store in row
    $sqlselect="select requesterEMAIL,Title,Destination from  `$sealSTAT` where illNUB='$reqnumb'  LIMIT 1 ";
    $result = mysqli_query($db, $sqlselect);
    $row = mysqli_fetch_array($result);

    // Escape values for security
    $respnote = mysqli_real_escape_string($db, $respnote);
    $resfill = mysqli_real_escape_string($db, $resfill);
    $sqlupdate = "UPDATE `$sealSTAT` SET `Fill` =  '6', `Title` = '', `Author` = '', `pubdate` = '', `reqisbn` = '', `reqissn` = '', `itype` = '', `Call Number` = '', `article` = '', `needbydate` = '', `patronnote` = '', `DueDate` = '', `emailsent` = '1' , `responderNOTE` =  '$respnote' WHERE `illNUB` = '$reqnumb'";

    if (mysqli_query($db, $sqlupdate)) {
        echo "Thank you.  Your response has been recorded to the request<br><br>";
        // Setup the note data to be in email
        $respnote=stripslashes($respnote);
        if (strlen($respnote)>0) {
            $respnote="The requesting library has noted the following <br> $respnote";
        }

        
        $title    = $row["Title"];
        $requesterEMAIL= $row['requesterEMAIL'];
        $destlib= $row['Destination'];
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


        $headers = "From: Southeastern SEAL <donotreply@senylrc.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        // sending canceled email#####
        if ($resfill=='6') {

            // Setting up email notification
            $message = "ILL request $reqnumb for $title has been canceled <br><br>$respnote ";
            // Setup php email headers
            $to=$requesterEMAIL;
            // $to="spalding@senylrc.org";
            $subject = "ILL Request Canceled ILL# $reqnumb  ";
            // SEND request an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            // mail has been sent to zack at seal for development
            //$destemail_to="spalding@senylrc.org";
            //$to="spalding@senylrc.org";
            mail($to, $subject, $message, $headers, "-f donotreply@senylrc.org");
            mail($destemail_to, $subject, $message, $headers, "-f donotreply@senylrc.org");
        }
    } else {
        echo "Unable to record the answer for ILL request $reqnumb please call SENYLRC to report this error";
    }
    // No notes but answering to cancel ILL request#########
} else {
    // The Request will be canceled #############################################
    if ($reqanswer=='3') {
             // Generate web message
            echo "Please click the submit button to confirm you want to cancel this request.<br>  Thank You."; ?>
       <br><br><h4>Notes about the cancelation</h4>
       <form action="/cancel" method="post">
       <input type='hidden' name='num' value= '<?php echo $reqnumb ?>' '>
       <input type='hidden' name='fill' value='6'>
       <textarea name='respondnote' rows="4" cols="50"></textarea><br>
       <input type="submit" value="Submit">
       </form>
        <?php
    }
    // End if statement if we are updating the note box####
}
mysqli_close($db);

?>
