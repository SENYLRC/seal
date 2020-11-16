<?php

function getWorkingDays($startDate,$endDate,$holidays){
    // do strtotime calculations just once
    $endDate = strtotime($endDate);
    $startDate = strtotime($startDate);

//debuing
//echo "start date".$startDate." End date ".$endDate." \n";
    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
    //We add one to inlude both dates in the interval.
    $days = ($endDate - $startDate) / 86400 + 1;
//debuging
//echo "days difference ".$days." \n";
    $no_full_weeks = floor($days / 7);
    $no_remaining_days = fmod($days, 7);

//echo "# full weeks ".$no_full_weeks." # remaing days ".$no_remaining_days."\n";
    //It will return 1 if it's Monday,.. ,7 for Sunday
    $the_first_day_of_week = date('N', $startDate);
    $the_last_day_of_week = date('N', $endDate);

//echo "first of day week ".$the_first_day_of_week." \n";
//echo "last day of week ".$the_last_day_of_week." \n";
    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.

    if ($the_first_day_of_week <= $the_last_day_of_week) {
        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
    }
    else {
        // (edit by Tokes to fix an edge case where the start day was a Sunday
        // and the end day was NOT a Saturday)

        // the day of the week for start is later than the day of the week for end
        if ($the_first_day_of_week == 7) {
            // if the start date is a Sunday, then we definitely subtract 1 day
            $no_remaining_days--;

            if ($the_last_day_of_week == 6) {
                // if the end date is a Saturday, then we subtract another day
                $no_remaining_days--;
            }
        }
        else {
//echo "start date was a weekend with num of remaining days ".$no_remaining_days." \n";
            // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
            // so we skip an entire weekend and subtract 2 days
            $no_remaining_days -= 2;
        }
    }
//echo "days remaining ".$no_remaining_days." \n";
    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
   $workingDays = $no_full_weeks * 5;
    if ($no_remaining_days > 0 )
    {
      $workingDays += $no_remaining_days;
    }

    //We subtract the holidays
    foreach($holidays as $holiday){
        $time_stamp=strtotime($holiday);
        //If the holiday doesn't fall in weekend
        if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
            $workingDays--;
    }
    return $workingDays;
}


$holidays=array("2021-01-01","2021-01-18","2020-02-17","2020-05-25","2020-09-07","2020-10-12","2020-10-31","2020-11-11","2020-11-26","2020-1
1-27","2020-12-25","2020-12-28","2020-12-29","2020-12-30","2020-12-31");



#####Connect to database
require 'seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);

$sqlselect="select * from `SENYLRC-SEAL2-STATS` where emailsent='0' and fill='3'";
$retval = mysqli_query($db,$sqlselect);
$GETLISTCOUNT = mysqli_num_rows ($retval);

while ($row = mysqli_fetch_assoc($retval)) {
			$timestamp	= $row["Timestamp"];
			$destination = $row["Destination"];
			$illnum	= $row["illNUB"];
			$title	= $row["Title"];
			$author	= $row["Author"];
			$itype	= $row["Itype"];
			$pubdate	= $row["pubdate"];
                        $isbn        = $row["reqisbn"];
                        $issn        = $row["reqissn"];
			$itemavail	= $row["Available"];
			$article    = $row["article"];
			$inst	= $row["Requester lib"];
			$address	= $row["saddress"];
			$caddress	= $row["caddress"];
			$needbydatet	= $row["needbydate"];
                        $reqnote      =  $row["reqnote"];
			$fname	= $row["Requester person"];
			$email	= $row["requesterEMAIL"];
			$wphone	= $row["requesterPhone"];
			#Get just the date from time stampe
			$reqdate = substr($timestamp, 0, 10);
			#Calculate date what three days from request is
                        $calenddate= date( "Y-m-d", strtotime( "$reqdate +3 day" ) );

echo "my reqdate is ".$reqdate." my cal date ".$calenddate." \n";
			$nubworkdays= getWorkingDays($reqdate,$calenddate,$holidays);
echo "working days ".$nubworkdays." \n";
			if ($nubworkdays < '3'){
				$diff =  3 - $nubworkdays;
                          $diff = round($diff);
			echo "the diff is ".$diff."\n";
                        echo "number of working days ".$nubworkdays."\n";
                       echo "calendar end date ".$calenddate."\n";
				$calenddate= date( "Y-m-d", strtotime( "$calenddate  +$diff day" ) );
			echo "new working date ".$calenddate." \n";
			}else{
   				echo "don't need to add time\n";
				$diff='0';
			}
			$today = date("Y-m-d");
			echo "ill number".$illnum." working days ".$nubworkdays." \n";


			###Get the Destination
            $GETLISTSQLDESTEMAIL="SELECT `ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc LIKE '$destination'  limit 1";
            $resultdestemail=mysqli_query($db, $GETLISTSQLDESTEMAIL);
	        while ($rowdesteamil = mysqli_fetch_assoc($resultdestemail)) {
				$destemail=$rowdesteamil["ILL Email"];
			}
			$destemailarray = explode(';', $destemail);

			if ($calenddate < $today) {
#Use to run script now and ignore days			if ($calenddate = $calenddate) {
				###Will now send out the reminders if we past 3 working days

				#########SETUP email
				#Well set these to white space if they are empty to prevent an error message
				if ( empty($needbydatet)) $needbydatet='';
    				if ( empty($reqnote)) $reqnote='';
				if ( empty($isbn)) $isbn='';
				if ( empty($issn)) $issn='';
	                        if ( empty($itemcall)) $itemcall='';
                                if ( empty($lname)) $lname='';

					if ( empty($arttile)) $article='';
						######Copy of message sent to the requester
						$messagereq = "An ILL request ($illnum)has been created for the following: <br><br>
						Title: $title <br>
						Author: $author<br>
						Item Type: $itype<br>
						Publication Date: $pubdate<br>
						$isbn<br>
      						$issn<br>
						Call Number: $itemcall <br>
						Availability Status: $itemavail<br>
						$article<br><br>
						The title is requested by the following library:<br>
						$inst<br>
						$address<br>$caddress<br><br>
						$needbydatet<br>
  						$reqnote<br>
						The request was created by:<br>
						$fname $lname<br>
						$email<br>
						$wphone<br>
						<br>";

						######Message for the destination library
						$messagedest = "An ILL request ($illnum)has been created for the following: <br><br>
						Title: $title <br>
						Author: $author<br>
						Item Type: $itype<br>
						Publication Date: $pubdate<br>
						$isbn<br>
      	 					$issn<br>
						Call Number: $itemcall <br>
						Availability Status: $itemavail<br>
						$article<br><br><br>
						The title is request to delivered to the following institution:<br>
						$inst<br>
						$address<br>$caddress<br><br>
						$needbydatet<br>
						$reqnote<br>
						The request was created by:<br>
						$fname $lname<br>
						$email<br>
						$wphone<br>
						<br>
						Will you fill this request?  <a href='http://seal2.senylrc.org/respond?num=$illnum&a=1' >Yes
</a> &nbsp&nbsp&nbsp&nbsp                  <a href='http://seal2.senylrc.org/respond?num=$illnum&a=0' >No</a>
						<br>";

						#######Set email subject for request
						$subject = "REMINDER ILL Request from $inst ILL# $illnum";
	                    		  	$subject = html_entity_decode (  $subject, ENT_QUOTES, 'UTF-8' );
						echo "$subject\n";
						#Set email to me for testing
						#$destemail = 'spalding@senylrc.org';
						#$email='spalding@senylrc.org';


						#####SEND EMAIL to Detestation Library with DKIM sig
                                                $email_to = implode(',', $destemailarray);
                                                $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
                                                $headers .= "MIME-Version: 1.0\r\n";
                                                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                                                mail($email_to, $subject, $messagedest, $headers, "-f ill@senylrc.org");


                                                #####SEND a copy of EMAIL to requester with DKIM sig
                                                $headers = "From: SENYLRC SEAL <sealillsystem@senylrc.org>\r\n" ;
                                                $headers .= "MIME-Version: 1.0\r\n";
                                                $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

                                                $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
                                                $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
                                                mail($email, $subject, $messagereq, $headers, "-f ill@senylrc.org");

						$sqlupdate = "UPDATE `SENYLRC-SEAL2-STATS` SET `emailsent` = '2' , `responderNOTE` =  'REMIN
DER MSG Sent' WHERE `illNUB` = '$illnum'";
						mysqli_query($db, $sqlupdate);
			}
}
?>
