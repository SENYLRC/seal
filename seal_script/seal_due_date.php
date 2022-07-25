<?php

#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);
$today = date("Y-m-d");
$fivedays = date('m/d/Y', strtotime('+5 days'));

$sqlselect="SELECT * FROM `SENYLRC-SEAL2-STATS` WHERE `DueDate` = '".$fivedays."' and `receiveAccount` IS NOT NULL and `returnAccount` IS NULL AND `checkinAccount` IS NULL ORDER BY `index` ASC";
$retval = mysqli_query($db,$sqlselect);
$GETLISTCOUNT = mysqli_num_rows ($retval);
while ($row = mysqli_fetch_assoc($retval)) {
  $timestamp      = $row["Timestamp"];
  $destination = $row["Destination"];
  $illnum = $row["illNUB"];
  $title  = $row["Title"];
  $author = $row["Author"];
  $itype  = $row["Itype"];
  $pubdate        = $row["pubdate"];
  $isbn        = $row["reqisbn"];
  $issn        = $row["reqissn"];
  $article    = $row["article"];
  $email  = $row["requesterEMAIL"];

  $duedate = $row["DueDate"];

  #########SETUP email
  #Well set these to white space if they are empty to prevent an error message
  if ( empty($reqnote)) $reqnote='';
  if ( empty($isbn)) $isbn='';
  if ( empty($issn)) $issn='';
  if ( empty($itemcall)) $itemcall='';
  if ( empty($lname)) $lname='';
  if ( empty($arttile)) $article='';
    ######Copy of message sent to the requester
    $messagereq = "ILL# $illnum has a due date approaching: $duedate <br><br>
      Title: $title <br>
      Author: $author<br>
      Item Type: $itype<br>
      Publication Date: $pubdate<br>
      $isbn<br>
      $issn<br>
      Call Number: $itemcall <br>
      $article<br><br>
      <br>"
      <br><hr style='width:200px;text-align:left;margin-left:0'><Br>
      This is an automated message from the SEAL ILL System. Responses to this email will be sent back to staff at Southeastern NY Library Resources Council. If you would like to contact the other
library in this ILL transaction, email.";
    #######Set email subject for request
    $subject = "SEAL Approaching Due Date: for ILL# $illnum";
    $subject = html_entity_decode (  $subject, ENT_QUOTES, 'UTF-8' );
    echo "$subject\n";
    #Set email to me for testing
    #$destemail = 'spalding@senylrc.org';
    #$email='spalding@senylrc.org';
    #####SEND a copy of EMAIL to requester with DKIM sig
    $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
    $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
    mail($email, $subject, $messagereq, $headers, "-f ill@senylrc.org");



}
?>
