<?php
##Being removed to page called lender-history.?>

<?php

$email=$_REQUEST["email"];
$address=$_REQUEST["address"];
$caddress=$_REQUEST["caddress"];
$wphone=$_REQUEST["wphone"];




if (isset($_REQUEST['reqLOCcode'])) {
    $reqLOCcode = $_REQUEST['reqLOCcode'];
}
if (isset($_REQUEST['bibauthor'])) {
    $author = $_REQUEST['bibauthor'];
}
if (isset($_REQUEST['bibtitle'])) {
    $title = $_REQUEST['bibtitle'];
}
if (isset($_REQUEST['destination'])) {
    $destination = $_REQUEST['destination'];
}
if (isset($_REQUEST['bibtype'])) {
    $itype = $_REQUEST['bibtype'];
}
if (isset($_REQUEST['pubdate'])) {
    $pubdate = $_REQUEST['pubdate'];
}
if (isset($_REQUEST['isbn'])) {
    $isbn = $_REQUEST['isbn'];
}
if (isset($_REQUEST['issn'])) {
    $issn = $_REQUEST['issn'];
}
if (isset($_REQUEST['needbydate'])) {
    $needbydate = $_REQUEST['needbydate'];
}
if (isset($_REQUEST['reqnote'])) {
    $reqnote = $_REQUEST['reqnote'];
}

#Pull all the artilce files and then combine into one variable
if (isset($_REQUEST['arttile'])) {
    $arttile = $_REQUEST['arttile'];
}
if (isset($_REQUEST['artauthor'])) {
    $artauthor = $_REQUEST['artauthor'];
}
if (isset($_REQUEST['artissue'])) {
    $artissue = $_REQUEST['artissue'];
}
if (isset($_REQUEST['artvolume'])) {
    $artvolume = $_REQUEST['artvolume'];
}
if (isset($_REQUEST['artpage'])) {
    $artpage = $_REQUEST['artpage'];
}
if (isset($_REQUEST['artmonth'])) {
    $artmonth = $_REQUEST['artmonth'];
}
if (isset($_REQUEST['artyear'])) {
    $artyear = $_REQUEST['artyear'];
}
if (isset($_REQUEST['artcopyright'])) {
    $artcopyright = $_REQUEST['artcopyright'];
}
$article="Article Title: ". $arttile ." <br>Article Author: ". $artauthor ." <br>Volume: ". $artvolume ."<br>Issue: ". $artissue ."<br> Pages: ". $artpage ." <br>Year: ".$artyear." <br>Month:  ".$artmonth."<br>Copyright: ".$artcopyright." ";
if (strlen($needbydate)>0) {
    $needbydatet="This item is needed by $needbydate";
}
if (strlen($reqnote)>0) {
    $reqnote="Note: $reqnote";
}
if (strlen($isbn)>2) {
    $isbn="ISBN: $isbn";
}
if (strlen($issn)>2) {
    $issn="ISSN: $issn";
}
if ((strlen($isbn)<2)&&(strlen($issn)<2)) {
    $isbn="ISBN: none";
}

#Requesting person library system, used for stats
####Pull the information of the person making the request
global $user;   // load the user entity so to pick the field from.
$user_contaning_field = user_load($user->uid);  // Check if we're dealing with an authenticated user
if ($user->uid) {    // Get field value;
$field_home_library_system =   field_get_items('user', $user_contaning_field, 'field_home_library_system');
    $reqsystem=$field_home_library_system[0]['value'];
    $field_first_name = field_get_items('user', $user_contaning_field, 'field_first_name');
    $field_last_name = field_get_items('user', $user_contaning_field, 'field_last_name');
    $field_your_institution = field_get_items('user', $user_contaning_field, 'field_your_institution');
    $fname = $field_first_name[0]['value'];
    $lname = $field_last_name[0]['value'];
    $inst= $field_your_institution[0]['value'];
    $wholename = "$fname $lname";
}

#Split Destination contents
list($libcode, $library, $destsystem, $itemavail, $itemcall, $itemlocation, $destemail, $destloc) = explode(":", $destination);


####Put the dest email in an arrary in case the library has more than one person who gets the message
$destemailarray = explode(';', $destemail);


#Check to see if data was posted to forum
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

########Insert request into Database
    $today = date('Y-m-d H:i:s');

    #####Connect to database
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);

    ####Add escape for title, author, call number, Library name, and Requester Name
    $ititle = mysqli_real_escape_string($db, $title);
    $article = mysqli_real_escape_string($db, $article);
    $iauthor = mysqli_real_escape_string($db, $author);
    $pubdate = mysqli_real_escape_string($db, $pubdate);
    $isbn = mysqli_real_escape_string($db, $isbn);
    $issn = mysqli_real_escape_string($db, $issn);
    $itemcall = mysqli_real_escape_string($db, $itemcall);
    $itemlocation = mysqli_real_escape_string($db, $itemlocation);
    $itype = mysqli_real_escape_string($db, $itype);
    $itemavail = mysqli_real_escape_string($db, $itemavail);
    $inst = mysqli_real_escape_string($db, $inst);
    $fname = mysqli_real_escape_string($db, $fname);
    $lname = mysqli_real_escape_string($db, $lname);
    $email = mysqli_real_escape_string($db, $email);
    $library = mysqli_real_escape_string($db, $library);
    $needbydate = mysqli_real_escape_string($db, $needbydate);
    $reqnote = mysqli_real_escape_string($db, $reqnote);
    $destloc = mysqli_real_escape_string($db, $destloc);
    $reqLOCcode = mysqli_real_escape_string($db, $reqLOCcode);
    $wphone = mysqli_real_escape_string($db, $wphone);
    $saddress = mysqli_real_escape_string($db, $address);
    $caddress = mysqli_real_escape_string($db, $caddress);

    $destloc = trim($destloc);
    $reqLOCcode = trim($reqLOCcode);

    $illiadchecksql = "SELECT IlliadURL,Illiad,APIkey,LibEmailAlert FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$destloc'";
    $illiadGETLIST = mysqli_query($db, $illiadchecksql);
    $illiadGETLISTCOUNT = '1';
    $illiadrow = mysqli_fetch_assoc($illiadGETLIST);
    $libilliadurl = $illiadrow["IlliadURL"];
    $libilliad = $illiadrow["Illiad"];
    $libilliadkey = $illiadrow["APIkey"];
    $libemailalert = $illiadrow["LibEmailAlert"];
    #####The SQL statement to insert for Stats and to recall if needed in the future
    $sql = "INSERT INTO `seal`.`SENYLRC-SEAL2-STATS` (`illNUB`,`Title`,`Author`,`pubdate`,`reqisbn`,`reqissn`,`itype`,`Call Number`,`Location`,`Available`,`article`,`needbydate`,`reqnote`,`Destination`,`DestSystem`,`Requester lib`,`Requester LOC`,`ReqSystem`,`Requester person`,`requesterEMAIL`,`Timestamp`,`Fill`,`responderNOTE`,`requesterPhone`,`saddress`,`caddress`,`shipMethod`,`returnNote`,`checkinTimeStamp`,`IlliadDataResponse`,`IlliadTransID`,`IlliadStatus`)
VALUES ('0','$ititle','$iauthor','$pubdate','$isbn','$issn','$itype','$itemcall','$itemlocation','$itemavail','$article','$needbydate','$reqnote','$destloc','$destsystem','$inst','$reqLOCcode','$reqsystem','$fname $lname','$email','$today','3','','$wphone','$saddress','$caddress','','','','','','')";

    #echo $sql;
    if (mysqli_query($db, $sql)) {
        ########Get the SQL id and create a ILL Number
        $sqlidnumb= mysqli_insert_id($db);
        $yearid=date('Y');
        $illnum="$yearid-$sqlidnumb";
        $sqlupdate = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `illNUB` =  '$illnum' WHERE `index` = $sqlidnumb";

        #########Send to ILLiad via API

        if ($libilliad=='1') {
            $sqlseloclc = "SELECT loc,name,`ILL Email`,address2,address3,OCLC,`system` FROM `SENYLRC-SEAL2-Library-Data` WHERE `loc`='$reqLOCcode'";
            //echo $sqlseloclc ;
            $sqlseloclcGETLIST = mysqli_query($db, $sqlseloclc);
            $sqlseloclcGETLISTCOUNT = '1';
            $sqlseloclcrow = mysqli_fetch_assoc($sqlseloclcGETLIST);
            $libreqOCLC = $sqlseloclcrow["oclc"];
            $libreqLOC = $sqlseloclcrow["loc"];
            $libreqemail = $sqlseloclcrow["ILL Email"];
            $libreqname = $sqlseloclcrow["name"];
            $libreqsystem =  $sqlseloclcrow["system"];
            $libreqaddress2 = $sqlseloclcrow["address2"];
            $libreqaddress3 = $sqlseloclcrow["address3"];
            $libreqaddress3=trim($libreqaddress3);
            $libreqaddress3 = str_replace(',', '', $libreqaddress3);
            $pieces = explode(" ", $libreqaddress3);
            $libreqcity= $pieces[0];
            $libreqstate= $pieces[1];
            $libreqzip= $pieces[2];

            $sqlilliadmp = "SELECT * FROM `SENYLRC-SEAL2-ILLIAD-ADD-MAPPING` WHERE `LOC`='$reqLOCcode' and `illiadID`='$destloc'";
            $sqlilliadmpGETLIST = mysqli_query($db, $sqlilliadmp);
            $sqlilliadmpGETLISTCOUNT = '1';
            $sqlilliadmprow = mysqli_fetch_assoc($sqlilliadmpGETLIST);
            $illiadADDnumb  = $sqlilliadmprow["illiadADDnumb"];
            $illiadLIBSymbol =  $sqlilliadmprow["illiadLIBSymbol"];
            #Add slashes to these string to prevent coding issue
            $ititle=addslashes($ititle);
            $iauthor=addslashes($iauthor);

            if ($reqLOCcode=='nnepsu') {
                //if this is New Paltz we will set some fields
                #Store data for request in array
                if (empty($arttile)) {
                     //set the special instruction to the correct lib systems
                    $specialinst= "ELD - ".$destsystem." ";
                    $jsonstr = array( 'Username' =>'Lending','WantedBy'=>'SEAL Request: This is a book loan','LendingString'=> 'This is a book loan', 'ProcessType'=>Lending,'LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'SpecIns'=>$specialinst,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'ILLNumber'=>$illnum ,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail);
                } else {
                    $jsonstr = array('Username' =>'Lending', 'ProcessType'=>Lending,'LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'SpecIns'=>$specialinst,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'PhotoArticleTitle'=>$arttile,'PhotoArticleAuthor'=>$artauthor,'PhotoJournalVolume'=>$artvolume,'PhotoJournalIssue'=>$artissue,'PhotoJournalYear'=>$artyear,'PhotoJournalInclusivePages'=>$artpage,'ISSN'=>$issn,'ILLNumber'=>$illnum,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail );
                }
            } else {
                //go with defualts

                #Store data for request in array
                if (empty($arttile)) {
                    $jsonstr = array( 'Username' =>'Lending','LendingString'=> 'This is a book loan', 'ProcessType'=>Lending,'LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'ILLNumber'=>$illnum ,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail);
                } else {
                    $jsonstr = array('Username' =>'Lending', 'ProcessType'=>Lending,'LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'PhotoArticleTitle'=>$arttile,'PhotoArticleAuthor'=>$artauthor,'PhotoJournalVolume'=>$artvolume,'PhotoJournalIssue'=>$artissue,'PhotoJournalYear'=>$artyear,'PhotoJournalInclusivePages'=>$artpage,'ISSN'=>$issn,'ILLNumber'=>$illnum,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail );
                }
            }

            #Enocde the array in to json data
            $json_enc=json_encode($jsonstr);

            //just so we can see this on screen
            //echo "<br /><br /><br />";
            //echo $json_enc;
            //echo "<br /><br /><br />";
            // variables to pass through cURL

            define("ILLIAD_REQUEST_TOKEN_URL", $libilliadurl);

            $key = $libilliadkey;
            // create the cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, ILLIAD_REQUEST_TOKEN_URL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_enc);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // commenting this out prints to screen (via echo)
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                  "Content-Type: application/json",
                  "Content-Length: " . strlen($json_enc),
                  "ApiKey: $key")
            );

            // make the call
            if (!curl_errno($ch)) {
                // $output contains the output string
                $output = curl_exec($ch);
            }

            // close curl resource to free up system resources
            curl_close($ch);


            // print the results of the call to the screen
            echo "<!--API output-->";
            echo "<!--".$output."-->";
            $output_decoded = json_decode($output, true);
            $illiadtxnub= $output_decoded['TransactionNumber'];
            $illstatus = $output_decoded['TransactionStatus'];

            if (strlen($illiadtxnub)<4) {
                $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                $messagereq = "Request did not go to ILLiad Ill ".$illnum." ";
                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                mail("spalding@senylrc.org", "ILLiad Failure", $messagereq, $headers, "-f noc@senylrc.org");
            } //end check if ILLad transaction did not happen

            //save API output to the request
            $sqlupdate2 = "UPDATE `seal`.`SENYLRC-SEAL2-STATS` SET `IlliadStatus` = '$illstatus', `IlliadTransID` = '$illiadtxnub', `IlliadDataResponse` =  '$output' WHERE `index` = $sqlidnumb";
            //echo $sqlupdate2;
            mysqli_query($db, $sqlupdate2);
        }#end the $libilliad check

        ############################This will generate the web page response
        echo "Your ILL number is $illnum, your request has been emailed to $library for the following<br>
      Title: $title.<br>
      Author: $author<br>
      Publication Date: $pubdate<br><br>

      A copy of this request has also been emailed to the requester $wholename at $email " ;
        mysqli_query($db, $sqlupdate);


        #########SETUP email
        #Well set these to white space if they are empty to prevent an error message
        if (empty($needbydatet)) {
            $needbydatet='';
        }
        if (empty($reqnote)) {
            $reqnote='';
        }
        if (empty($arttile)) {
            $article='';
        }

        ######Copy of message sent to the requester
        $messagereq = "An ILL request ($illnum) has been created for the following: <br><br>
       Title: $title <br>
       Author: $author<br>
       Item Type: $itype<br>
       Publication Date: $pubdate<br>
       $isbn<br>
       $issn<br>
      Call Number: $itemcall <br>
      Availability Status: $itemavail<br>
      Location: $itemlocation<br>
       $article<br><br>
     <a href='http://seal2.senylrc.org/cancel?num=$illnum&a=3' >Do you need to cancel this request? </a>
     <br><br>
       The title is requested by the following library:<br>
      $inst<br>
       $address<br>$caddress<br><br>
      $needbydatet<br>
      $reqnote<br>  <br><br>
     The request was created by:<br>
     $wholename<br>
     $email<br>
      $wphone<br>";

        ######Message for the destination library
        $messagedest = "An ILL request ($illnum) has been created for the following: <br><br>
       Title: $title <br>
       Author: $author<br>
       Item Type: $itype<br>
       Publication Date: $pubdate<br>
      $isbn<br>
       $issn<br>
      Call Number: $itemcall <br>
      Availability Status: $itemavail<br>
      Location: $itemlocation<br>
       $article<br><br><br>
       The title is requested by the following library:<br>
      $inst<br>
       $address<br>$caddress<br><br>
      $needbydatet<br>
      $reqnote<br>
     The request was created by:<br>
     $wholename<br>
     $email<br>
     $wphone<br>
    <br>
    <strong>Note regarding Empire Library Delivery:</strong> Please be aware that not all libraries / library systems have re-started Empire Library Delivery. Be sure to check <a href='https://docs.google.com/spreadsheets/d/1cg7-kNJ0GeJ9ZsJB01GZk_jhS8mUzGWO66gq2vbyVew/edit#gid=2039338721'>this page</a> to see an up-to-date status of <strong>Empire Library Delivery libraries</strong> before sending materials.
    <br><br>
     Will you fill this request?  <a href='http://seal2.senylrc.org/respond?num=$illnum&a=1' >Yes</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='http://seal2.senylrc.org/respond?num=$illnum&a=0' >No</a>
     <br>";

        #######Set email subject for request
        $subject = "ILL Request from $inst ILL# $illnum";

        $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');


        #Don't send a message to destination if they don't want it
        if ($libemailalert=='1') {
            #####SEND EMAIL to Detestation Library
            $email_to = implode(',', $destemailarray);
            $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;

            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);


            mail($email_to, $subject, $messagedest, $headers, "-f ill@senylrc.org");
        }

        #####SEND a copy of EMAIL to requester
        $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


        $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);


        mail($email, $subject, $messagereq, $headers, "-f ill@senylrc.org");

        #########Ask the requester if they would like to do another request
        echo "<br><br><a href='http://seal2.senylrc.org'>Would you like to do another request?<a><br>";
    } else {
        #Something happen and could not create a requiest
        echo "Error: " . $sql . "<br>" . mysqli_error($db);
        echo "Unable to create request due a technical issue, if this happens again please contact SENYLRC Tech Support";
    }
    mysqli_close($db);
}
