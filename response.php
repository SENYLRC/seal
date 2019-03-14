<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
     $("#datepicker").datepicker();
  });
  </script>
<?php

##Get values
$reqnumb=$_REQUEST["num"];
if (isset($_REQUEST['a'])) {
    $reqanswer = $_REQUEST['a'];
} else {
    $reqanswer='';
}



#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

####Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$reqanswer = mysqli_real_escape_string($db, $reqanswer);


###Process any notes from the lender#############################
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $respnote=$_REQUEST["respondnote"];
    $resfill=$_REQUEST["fill"];
    $duedate=$_REQUEST["duedate"];
    $nofillreason=$_REQUEST["nofillreason"];
    $shipmethod=$_REQUEST["shipmethod"];
    ####Escape values for security
    $respnote = mysqli_real_escape_string($db, $respnote);
    $resfill = mysqli_real_escape_string($db, $resfill);
    $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `emailsent` = '1', `Fill` = '$resfill'  , `responderNOTE` =  '$respnote',`shipMethod`='$shipmethod',`DueDate`='$duedate',`ReasonNotFilled`='$nofillreason'  WHERE `illNUB` = '$reqnumb'";

    if (mysqli_query($db, $sqlupdate)) {
        echo "Thank you.  Your response has been recorded to the request.<br><a href='/lender-history'>Click here to view your lender history</a><br>";
        ####Setup the note data to be in email
        $respnote=stripslashes($respnote);
        if (strlen($respnote)>0) {
            $respnote="The lending library has noted the following <br> $respnote";
        }
        $sqlselect="select responderNOTE,requesterEMAIL,Title,Destination from  `seal`.`SENYLRC-SEAL2-STATS` where illNUB='$reqnumb'  LIMIT 1 ";
        $result = mysqli_query($db, $sqlselect);
        $row = mysqli_fetch_array($result) ;
        $title =$row['Title'];
        $requesterEMAIL=$row['requesterEMAIL'];
        $destlib=$row['Destination'];
        ###Get the Destination Name
        $GETLISTSQLDEST="SELECT`Name`, `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$destlib'  limit 1";
        $resultdest=mysqli_query($db, $GETLISTSQLDEST);
        while ($rowdest = mysqli_fetch_assoc($resultdest)) {
            $destlib=$rowdest["Name"];
            $destemail=$rowdest["ILL Email"];
        }
        #In case the ILL email for the destination library is more than one, break it down to comma for php mail
        $destemailarray = explode(';', $destemail);
        $destemail_to = implode(',', $destemailarray);

        $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";



        ####sending filled email#####
        if ($resfill=='1') {

                #######Setting up email notification
            if ($shipmethod=="usps") {
                $shiptxt='US Mail';
            }
            if ($shipmethod=="mhls") {
                $shiptxt='Mid-Hudson Courier';
            }
            if ($shipmethod=="rcls") {
                $shiptxt='RCLS Courier';
            }
            if ($shipmethod=="empire") {
                $shiptxt='Empire Delivery';
            }
            if ($shipmethod=="ups") {
                $shiptxt='UPS';
            }
            if ($shipmethod=="fedex") {
                $shiptxt='FedEx';
            }
            if ($shipmethod=="other") {
                $shiptxt='Other';
            }
            if ($shipmethod=="") {
                $shiptxt='';
            }
            $message = "Your ILL request $reqnumb for $title will be filled by $destlib <br>Due Date: $duedate<br><br>Shipped via: $shiptxt<br><br>$respnote ".
                                     "<br><br>Please email <b>".$destemail_to."</b> for future communications regarding this request ";
            #######Setup php email headers
            $to=$requesterEMAIL;
            $subject = "ILL Request Filled ILL# $reqnumb  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f ill@senylrc.org");

        ###Sending not filledmessage#########
        } else {
            #######Setting up email notification
            if ($nofillreason=='20') {
                $reasontxt='In Use';
            }
            if ($nofillreason=='21') {
                $reasontxt='Lost';
            }
            if ($nofillreason=='22') {
                $reasontxt='Non-Circulating';
            }
            if ($nofillreason=='23') {
                $reasontxt='Not on shelf';
            }
            $message = "Your ILL request $reqnumb for $title can not be filled by $destlib.<br>".
                "Reason request can not be filled: $reasontxt".
                "<br><br>$respnote<br><br> <a href='http://seal.senylrc.org'>Would you like to try a different library</a>?";
            #######Setup php email headers
            $to=$requesterEMAIL;
            $subject = "ILL Request Not Filled ILL# $reqnumb  ";
            #####SEND requester an email to let them know the request will be filled
            $message = preg_replace('/(?<!\r)\n/', "\r\n", $message);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            mail($to, $subject, $message, $headers, "-f ill@senylrc.org");
        }
    } else {
        echo "Unable to record answer for ILL request $reqnumb please call SENYLRC to report this error";
    }
    ####No notes but answering yes or no to ILL request#########
} else {
    ###################The Request will be filled##############################################
    if ($reqanswer=='1') {
        echo "Please click the submit button to confirm you will fill the request.<br>  Thank You."; ?>
      <br><br><h4>Please note the delivery method, tracking info, special handling, etc)</h4>
      <form action="/respond" method="post">
      <input type='hidden' name='num' value= '<?php echo $reqnumb ?>' '>
	   	<input type='hidden' name='fill' value='1'>
                 <input type='hidden' name='nofillreason' value='0'>
      <textarea name='respondnote' rows="4" cols="50"></textarea><br>
			Due Date:
      <input id="datepicker" name="duedate"/><br>
    Ship Method:
      <select name="shipmethod">
        <option value=""></option>
         <option value="usps">US Mail</option>
         <option value="mhls">Mid-Hudson Courier</option>
         <option value="rcls">RCLS Courier</option>
         <option value="empire">Empire Delivery</option>
         <option value="ups">UPS</option>
         <option value="fedex">FedEx</option>
         <option value="other">Other</option></select><br>
      <input type="submit" value="Submit">
      </form>
     	<?php
        #########The request will not be filled###########################
    } else {
        echo "Please click the submit button to confirm you can not fill the request"; ?>
    	<br><br><h4>Would you like to add a note about the decline?<br> </h4>
      <form action="/respond" method="post">
      <input type='hidden' name='num' value= '<?php echo $reqnumb ?>' '>
	    <input type='hidden' name='fill' value='0'>
      <select name="nofillreason">
    <option value="0">Reason</option>
     <option value="20">In Use</option>
     <option value="21">Lost</option>
     <option value="22">Non-Circulating</option>
     <option value="23">Not on shelf</option>
   </select><br><br>
      <textarea name='respondnote' rows="4" cols="50"></textarea><br>
      <input type="submit" value="Submit">
      </form>
		   <?php
    }###End if statement of yes or no#####
}#####End if statement if we are updating the note box####
mysqli_close($db);

?>
