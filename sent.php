<?php

// sent.php###
$illsystemhost = $_SERVER["SERVER_NAME"];
require '/var/www/seal_script/seal_function.php';
// Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

$fname=$_REQUEST["fname"];
$lname=$_REQUEST["lname"];

$email=$_REQUEST["email"];
$inst=$_REQUEST["inst"];
$address=$_REQUEST["address"];
$address2=$_REQUEST["address2"];
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
if (isset($_REQUEST['patronnote'])) {
    $patronnote = $_REQUEST['patronnote'];
}

// Pull all the articles files and then combine into one variable
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
if (strlen($patronnote)>0) {
    $patronnote="Patron: $patronnote";
}
if (strlen($isbn)>2) {
    $isbn="ISBN: $isbn";
}
if (strlen($issn)>2) {
    $issn="ISSN: $issn";
}

// Pull the information of the person making the request
$reqsystem=$field_home_library_system;
foreach ($_POST['libdestination'] as $destination) {
    list($libcode, $library, $destsystem, $itemavail, $itemcall, $itemlocation, $destemail, $destloc) = explode(":", $destination);

    // UnHTML encodes call numbers that might have strange characters
    $itemcall = htmlspecialchars_decode($itemcall, ENT_QUOTES);
    $libcode = htmlspecialchars_decode($libcode, ENT_QUOTES);
    $library = htmlspecialchars_decode($library, ENT_QUOTES);

    // Put the dest email in an array in case the library has more than one person who gets the message
    $destemailarray = explode(';', $destemail);
    // Check to see if data was posted to the forum
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Insert request into Database
        $today = date('Y-m-d H:i:s');

        // Add escape for the title, author, call number, Library name, and Requester Name
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
        $patronnote = mysqli_real_escape_string($db, $patronnote);
        $destloc = mysqli_real_escape_string($db, $destloc);
        $reqLOCcode = mysqli_real_escape_string($db, $reqLOCcode);
        $wphone = mysqli_real_escape_string($db, $wphone);
        $saddress = mysqli_real_escape_string($db, $address);
        $saddress2 = mysqli_real_escape_string($db, $address2);
        $caddress = mysqli_real_escape_string($db, $caddress);
        $itype = trim($itype);
        $ititle = trim($ititle);
        $iauthor = trim($iauthor);
        $email = trim($email);
        $library = trim($library);
        $inst = trim($inst);
        $destloc = trim($destloc);
        $caddress = trim($caddress);
        $saddress = trim($saddress);
        $reqLOCcode = trim($reqLOCcode);
        $wphone = trim($wphone);
        $fname = trim($fname);
        $lname = trim($lname);
        $pubdate = trim($pubdate);
        //get any illiead data
        $illiadchecksql = "SELECT IlliadDATE,IlliadURL,Illiad,APIkey,LibEmailAlert FROM `$sealLIB` WHERE `loc`='$destloc'";
        //for debuging
        //echo $illiadchecksql."<br>";
        $illiadGETLIST = mysqli_query($db, $illiadchecksql);
        $illiadGETLISTCOUNT = '1';
        $illiadrow = mysqli_fetch_assoc($illiadGETLIST);
        $libilliadurl = $illiadrow["IlliadURL"];
        $libilliaddate = $illiadrow["IlliadDATE"];
        $libilliad = $illiadrow["Illiad"];
        $libilliadkey = $illiadrow["APIkey"];
        $libemailalert = $illiadrow["LibEmailAlert"];
        // The SQL statement to insert for Stats and to recall if needed in the future
        $sql = "INSERT INTO `$sealSTAT` (`illNUB`,`Title`,`Author`,`pubdate`,`reqisbn`,`reqissn`,`itype`,`Call Number`,`Location`,`Available`,`article`,`needbydate`,`reqnote`,`patronnote`,`Destination`,`DestSystem`,`Requester lib`,`Requester LOC`,`ReqSystem`,`Requester person`,`requesterEMAIL`,`Timestamp`,`Fill`,`responderNOTE`,`requesterPhone`,`saddress`,`saddress2`,`caddress`)
 VALUES ('0','$ititle','$iauthor','$pubdate','$isbn','$issn','$itype','$itemcall','$itemlocation','$itemavail','$article','$needbydate','$reqnote','$patronnote','$destloc','$destsystem','$inst','$reqLOCcode','$reqsystem','$fname $lname','$email','$today','3','','$wphone','$saddress','$saddress2','$caddress')";
        //for testing
        //for testing
         //echo $sql."<br>";

        if (mysqli_query($db, $sql)) {
            //for debuggin
            //echo "SQL was good<br><br>";
            // Get the SQL id and create a ILL Number
            $sqlidnumb= mysqli_insert_id($db);
            $yearid=date('Y');
            $illnum="$yearid-$sqlidnumb";
            $sqlupdate = "UPDATE `$sealSTAT` SET `illNUB` =  '$illnum' WHERE `index` = $sqlidnumb";

                // Send to ILLiad via API
            if ($libilliad=='1') {
                $sqlseloclc = "SELECT loc,Name,`ill_email`,address2,address3,OCLC,`system` FROM `$sealLIB` WHERE `loc`='$reqLOCcode'";
                //for debugging
                //echo $sqlseloclc;
                $sqlseloclcGETLIST = mysqli_query($db, $sqlseloclc);
                $sqlseloclcGETLISTCOUNT = '1';
                $sqlseloclcrow = mysqli_fetch_assoc($sqlseloclcGETLIST);
                $libreqOCLC = $sqlseloclcrow["oclc"];
                $libreqLOC = $sqlseloclcrow["loc"];
                $libreqemail = $sqlseloclcrow["ill_email"];
                $libreqname = $sqlseloclcrow["Name"];
                $libreqsystem =  $sqlseloclcrow["system"];
                $libreqaddress2 = $sqlseloclcrow["address2"];
                $libreqaddress3 = $sqlseloclcrow["address3"];
                $libreqaddress3=trim($libreqaddress3);
                $libreqaddress3 = str_replace(',', '', $libreqaddress3);
                $pieces = explode(" ", $libreqaddress3);
                $libreqcity= $pieces[0];
                $libreqstate= $pieces[1];
                $libreqzip= $pieces[2];
        
                $sqlilliadmp = "SELECT * FROM `$sealILLiadMapping` WHERE `LOC`='$reqLOCcode' and `illiadID`='$destloc'";
                // echo $sqlilliadmp."<br>";
                $sqlilliadmpGETLIST = mysqli_query($db, $sqlilliadmp);
                $sqlilliadmpGETLISTCOUNT = '1';
                $sqlilliadmprow = mysqli_fetch_assoc($sqlilliadmpGETLIST);
                $illiadADDnumb  = $sqlilliadmprow["illiadADDnumb"];
                $illiadLIBSymbol =  $sqlilliadmprow["illiadLIBSymbol"];
                // Add slashes to these string to prevent coding issue
                $ititle=addslashes($ititle);
                $iauthor=addslashes($iauthor);   
                //Generate the due date, requrired for ILLiad Loans
                if (ctype_digit($libilliaddate)) {
                    $date = date("Y-m-d");
                    $illduedateCAL= date('Y-m-d', strtotime($date. ' + '.$libilliaddate.' days'));
                }
                //for testing
                //echo $illduedateCAL."part 2".$date."part 3".$libilliaddate."";
                // Store data for request in array
                if (empty($arttile)) {
                    //book request have to be sent as an article or API won't take them
                    //note about being a book loan is set so ILLiad users know to press loan radio button
                    $jsonstr = array( 'Username' =>'Lending','LendingString'=> $reqnote, 'RequestType'=>'Loan','DueDate'=>$illduedateCAL.'T00:00:00-04:00','ProcessType'=>'Lending','LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'ISSN'=>$isbn,'ILLNumber'=>$illnum ,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail);
                } else {
                    $jsonstr = array('Username' =>'Lending','LendingString'=> $reqnote, 'ProcessType'=>'Lending','LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'PhotoArticleTitle'=>$arttile,'PhotoArticleAuthor'=>$artauthor,'PhotoJournalVolume'=>$artvolume,'PhotoJournalIssue'=>$artissue,'PhotoJournalYear'=>$artyear,'PhotoJournalInclusivePages'=>$artpage,'ISSN'=>$issn,'ILLNumber'=>$illnum,'TAddress'=>$libreqname,'TAddress2'=>$libreqaddress2,'TCity'=>$libreqcity,'TState'=>$libreqcity,'TZip'=>$libreqzip,'TEMailAddress'=>$libreqemail );
                }
        
                // Enocde the array in to json data
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
                    $headers = "From: Southeastern SEAL <dontreply@CDCL.org>\r\n" ;
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                    $messagereq = "Request did not go to ILLiad Ill ".$illnum." ".$output." ";
                    $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                    mail("spalding@senylrc.org", "ILLiad Failure", $messagereq, $headers, "-f donotreply@senylrc.org");
                } //end check if ILLad transaction did not happen
        
                //save API output to the request
                $sqlupdate2 = "UPDATE `$sealSTAT` SET `IlliadStatus` = '$illstatus', `IlliadTransID` = '$illiadtxnub' WHERE `index` = $sqlidnumb";
                //echo $sqlupdate2;
        
                if (mysqli_query($db, $sqlupdate2)) {
                    //mysqli_query($db, $sqlupdate2);
                    //no error and everthing is fine
                } else {
                    // Something happen and could not update request, will email the sql to admin
                    $headers = "From: Southeastern SEAL <dontreply@CDCL.org>\r\n" ;
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
                    $messagereq = "UPDATE SENYLRC-SEAL2-STATS SET IlliadStatus = ".$illstatus.", IlliadTransID = ".$illiadtxnub." WHERE index = ".$sqlidnumb." ";
                    $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                    mail("spalding@senylrc.org", "sql update Failure", $messagereq, $headers, "-f donotreply@senylrc.org");
                }
            }// end the $libilliad check

            echo "Request <b>$illnum</b> has been emailed to <b>$library.</b><br>";
            mysqli_query($db, $sqlupdate);

            // SETUP email
            // We'll set these to white space if they are empty to prevent an error message
            if (empty($needbydatet)) {
                $needbydatet='';
            }
            if (empty($reqnote)) {
                $reqnote='';
            }
            if (empty($arttile)) {
                $article='';
            }
            // Copy of message sent to the requester
            $messagereq = "An ILL request ($illnum) has been created for the following: <br><br>
     Library: $library <br>
     Title: $title <br>
     Author: $author<br>
     Item Type: $itype<br>
     Publication Date: $pubdate<br>
     $isbn<br>
     $issn<br>
     Call Number: " . $itemcall . " <br>
     Availability Status: $itemavail<br>
     Location: $itemlocation<br>
     $article<br><br>
     <a href='https://$illsystemhost/cancel?num=$illnum&a=3' >Do you need to cancel this request? </a>
     <br><br>
     The title is requested by the following library:<br>
     $inst<br>
     $address<br>
     $address2<br>
     $caddress<br><br>
     Need by: $needbydate<br>
     Note from requestor: $reqnote<br><br><br>
     The request was created by:<br>
     $fname $lname<br>
     $email<br>
     $wphone<br>";
            //end of the message to the requester

            // Message for the destination library
            $messagedest = "An ILL request ($illnum) has been created for the following: <br><br>
     Library: $library <br>
     Title: $title <br>
     Author: $author<br>
     Item Type: $itype<br>
     Publication Date: $pubdate<br>
     $isbn<br>
     $issn<br>
     Call Number: " . $itemcall . " <br>
     Availability Status: $itemavail<br>
     Location: $itemlocation<br>
     $article<br><br><br>
     The title is requested by the following library:<br>
     $inst<br>
     $address<br>
     $address2<br>
     $caddress<br><br>
     Need by: $needbydate<br>
     Note from requestor: $reqnote<br><br><br>
     The request was created by:<br>
     $fname $lname<br>
     $email<br>
     $wphone<br><br>
     Will you fill this request?  <a href='https://$illsystemhost/respond?num=$illnum&a=1' >Yes</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='https://$illsystemhost/respond?num=$illnum&a=0' >No</a><br>";
            //end of the message to the destination
            //start of sending mail
            // Set email subject for request
            $subject = "NEW ILL Request from $inst ILL# $illnum";

            // SEND EMAIL to destination Library with DKIM Signature
            $email_to = implode(',', $destemailarray);
            $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "Southeastern SEAL" <donotreply@senylrc.org>' . "\r\n" . "Reply-to: " . $email . "\r\n" . 'Content-type: text/html; charset=utf8';

            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at seal for development
            //$email_to="mwakeman@senylrc.org";
            mail($email_to, $subject, $messagedest, $headers, "-f donotreply@senylrc.org");

            // SEND a copy of EMAIL to the requester with DKIM sig
            $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "Southeastern SEAL" <donotreply@senylrc.org>' . "\r\n" . "Reply-to: " . $email_to . "\r\n" . 'Content-type: text/html; charset=utf8';

            $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at seal for development
            //$email="mwakeman@senylrc.org";
            mail($email, $subject, $messagereq, $headers, "-f donotreply@senylrc.org");

            //end of sending mail
        } else {
            // Something happened, and I could not create a request
            echo "Error: " . $sql . "<br>" . mysqli_error($db);
            echo "Unable to create request due a technical issue, if this happens again, please contact CDCL Tech Support";
            echo "<br><br>";
        }// end if for SQL query check
    }//end if the check for POST

    // This will generate the web page response
    echo "<br>Details of your request(s):<br>";
    echo "Library: <b>$library</b><br>" ;
    echo "Title: <b>$title</b><br>" ;
    echo "Author: <b>$author</b><br>" ;
    echo "Publication Date: <b>$pubdate</b><br><br>" ;
    echo "A copy of this request has also been emailed to the requester $fname $lname at $email.<br>" ;
}//end foreach loop
mysqli_close($db);
// Ask the requester if they would like to make another request
?>
<br>
<button><a href='/'>Would you like to make another request?</a></button>
