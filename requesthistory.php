<?php
###requesthistory.php###

require '../seal_script/seal_function.php';


if (isset($_GET['loc'])) {
    $loc = $field_loc_location_code[0]['value'];
    $filter_yes="yes";
    $filter_no="yes";
    $filter_noans="yes";
    $filter_expire="";
    $filter_cancel="";
    $filter_cancel="";
    $filter_recevied="";
    $filter_return="";
    $filter_checkin="";
    $filter_days="365";
    $filter_destination="";
    $filter_illnum="";
} else {
    if (isset($_REQUEST['loc'])) {
        $loc = $field_loc_location_code[0]['value'];
        if (isset($_REQUEST['filter_illnum'])) {
            $filter_illnum = $_REQUEST['filter_illnum'];
        }
        if ($filter_illnum != "") { #resets the other options for the best possible ILL search
            $filter_yes="yes";
            $filter_no="yes";
            $filter_noans="yes";
            $filter_expire="yes";
            $filter_cancel="yes";
            $filter_cancel="yes";
            $filter_recevied="yes";
            $filter_return="yes";
            $filter_checkin="yes";
            $filter_days="all";
            $filter_destination="";
        } else {
            $loc = $field_loc_location_code[0]['value'];
            $filter_yes = (isset($_REQUEST['filter_yes']) ? $_REQUEST['filter_yes'] : "");
            $filter_no = (isset($_REQUEST['filter_no']) ? $_REQUEST['filter_no'] : "");
            $filter_noans = (isset($_REQUEST['filter_noans']) ? $_REQUEST['filter_noans'] : "");
            $filter_expire = (isset($_REQUEST['filter_expire']) ? $_REQUEST['filter_expire'] : "");
            $filter_cancel = (isset($_REQUEST['filter_cancel']) ? $_REQUEST['filter_cancel'] : "");
            $filter_recevied = (isset($_REQUEST['filter_recevied']) ? $_REQUEST['filter_recevied'] : "");
            $filter_return = (isset($_REQUEST['filter_return']) ? $_REQUEST['filter_return'] : "");
            $filter_checkin = (isset($_REQUEST['filter_checkin']) ? $_REQUEST['filter_checkin'] : "");
            $filter_days = (isset($_REQUEST['filter_days']) ? $_REQUEST['filter_days'] : "");
            $filter_destination = (isset($_REQUEST['filter_destination']) ? $_REQUEST['filter_destination'] : "");
            $filter_illnum = (isset($_REQUEST['filter_illnum']) ? $_REQUEST['filter_illnum'] : "");
        }
    } else {
        $loc = $field_loc_location_code[0]['value'];
        $filter_yes="yes";
        $filter_no="yes";
        $filter_noans="yes";
        $filter_expire="";
        $filter_cancel="";
        $filter_recevied="";
        $filter_return="";
        $filter_checkin="";
        $filter_days="365";
        $filter_destination="";
        $filter_illnum="";
    }
}

#Filter options
echo "<form action=".$_SERVER['REDIRECT_URL']." method='post'>";
echo "<input type='hidden' name='loc' value= '$loc'>";
echo "<p>Display Fill Status: ";
echo "<input type='checkbox' name='filter_yes' value='yes' " . checked($filter_yes) . ">Yes  ";
echo "<input type='checkbox' name='filter_no' value='yes' " . checked($filter_no) . ">No  ";
echo "<input type='checkbox' name='filter_noans' value='yes' " . checked($filter_noans) . ">No Answer  ";
echo "<input type='checkbox' name='filter_expire' value='yes' " . checked($filter_expire) . ">Expired  ";
echo "<input type='checkbox' name='filter_cancel' value='yes' " . checked($filter_cancel) . ">Canceled  ";
echo "<input type='checkbox' name='filter_recevied' value='yes' " . checked($filter_recevied) . ">Received  ";
echo "<input type='checkbox' name='filter_return' value='yes' " . checked($filter_return) . ">Return  ";
echo "<input type='checkbox' name='filter_checkin' value='yes' " . checked($filter_checkin) . ">Check In  ";
echo "for ";
echo "<select name='filter_days'>";
echo "<option value='90' " . selected("90", $filter_days) . ">90 days</option>";
echo "<option value='30' " . selected("30", $filter_days) . ">30 days</option>";
echo "<option value='60' " . selected("60", $filter_days) . ">60 days</option>";

echo "<option value='all' " . selected("all", $filter_days) . ">all days</option>";
echo "</select> ";
echo "<a href='".$_SERVER['REDIRECT_URL']."?clear=yes'>clear</a>  ";
echo "<input type=Submit value=Update><br>";
echo "ILL # <input name='filter_illnum' type='text' value='$filter_illnum'>  ";
echo "Destination <input name='filter_destination' type='text' value='$filter_destination'>";
echo "</p>";
echo "</form>";

#Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

#Sanitize data
$loc = mysqli_real_escape_string($db, $loc);

$SQLBASE="SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$sealSTAT` WHERE `Requester LOC` = '$loc'";
$SQLEND=" ORDER BY `index`  DESC";

if ($filter_days == "all") {
    $SQL_DAYS = "";
} else {
    $SQL_DAYS = " AND (DATE(`Timestamp`) BETWEEN NOW() - INTERVAL " . $filter_days . " DAY AND NOW() )";
}

if (strlen($filter_illnum) > 2) {
    $SQLILL = " AND `illNUB` LIKE '%" . $filter_illnum . "%'";
} else {
    $SQLILL = "";
}

if (strlen($filter_destination) > 2) {
    $SQL_Dest_Search="SELECT `loc` FROM `SENYLRC-SEAL2-Library-Data` where `Name` like '%$filter_destination%'";
    $PossibleDests=mysqli_query($db, $SQL_Dest_Search);
    while ($rowdest = mysqli_fetch_assoc($PossibleDests)) {
        $destloc=$rowdest["loc"];
        if (strlen($SQL_DESTINATION) > 2) {
            $SQL_DESTINATION = $SQL_DESTINATION . " OR `destination` = '$destloc'";
        } else {
            $SQL_DESTINATION = " AND (`destination` = '$destloc'";
        }
    }
    $SQL_DESTINATION = $SQL_DESTINATION . ")";
} else {
    $SQL_DESTINATION = "";
}

$SQLMIDDLE ='';
if ($filter_yes == "yes") {
    $SQLMIDDLE = "`fill`= 1 ";
}
if ($filter_no == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 0 ";
    } else {
        $SQLMIDDLE = "`fill`= 0 ";
    }
}
if ($filter_noans == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 3 ";
    } else {
        $SQLMIDDLE = "`fill`= 3 ";
    }
}
if ($filter_expire == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 4 ";
    } else {
        $SQLMIDDLE = "`fill`= 4 ";
    }
}
if ($filter_cancel == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 6 ";
    } else {
        $SQLMIDDLE = "`fill`= 6 ";
    }
}
if ($filter_checkin == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `checkinAccount` IS NOT NULL ";
    } else {
        $SQLMIDDLE = "`checkinAccount` IS NOT NULL ";
    }
} if ($filter_recevied == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `receiveAccount` IS NOT NULL AND `returnAccount` IS NULL ";
    } else {
        $SQLMIDDLE = "`receiveAccount` IS NOT NULL AND `returnAccount` IS NULL ";
    }
} if ($filter_return == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL ";
    } else {
        $SQLMIDDLE = " `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL ";
    }
}
$GETLISTSQL = $SQLBASE . $SQL_DESTINATION . $SQL_DAYS . $SQLILL . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
#echo $GETLISTSQL;
$GETLIST = mysqli_query($db, $GETLISTSQL);
$GETLISTCOUNTwhole = mysqli_num_rows($GETLIST);
echo "$GETLISTCOUNTwhole results<bR>";
echo "<table><TR><TH width='5%'>ILL #</TH><TH width='25%'>Title / Author</TH><TH>Type</TH><TH>Need By</TH><TH>Lender Destination & Contact</TH><TH>Borrower</TH><TH>Due Date & Shipping</TH><TH>Timestamp</TH><TH>Status</TH><TH>Action</TH></TR>";
$rowtype=1;
while ($row = mysqli_fetch_assoc($GETLIST)) {
    $illNUB = $row["illNUB"];
    $title = $row["Title"];
    $author = $row["Author"];
    $itype = $row["Itype"];
    $reqnote = $row["reqnote"];
    $lendnote = $row["responderNOTE"];
    $needby = $row["needbydate"];
    $dest = $row["Destination"];
    $reqp = $row["Requester person"];
    $reql = $row["Requester lib"];
    $timestamp = $row["Timestamp"];
    $shipmethod = $row["shipMethod"];
    $receiveaccount=$row['receiveAccount'];
    $returnaccount=$row['returnAccount'];
    $returnnote=$row['returnNote'];
    $returnmethod=$row['returnMethod'];
    $returndate=$row['returnDate'];
    $receivedate=$row['receiveDate'];
    $checkinAccount=$row['checkinAccount'];
    $checkindate=$row['checkinTimeStamp'];
    $duedate = $row["DueDate"];
    $renewNote= $row["renewNote"];
    $renewNoteLender = $row["renewNoteLender"];
    $renewAnswer=$row["renewAnswer"];

    $fill = $row["Fill"];
    $statustxt = itemstatus($fill, $receiveaccount, $returnaccount, $returndate, $receivedate, $checkinAccount, $checkindate);
    $shiptxt=shipmtotxt($shipmethod);
    $returnmethodtxt=shipmtotxt($returnmethod);
    $dest=trim($dest);
    #Get the Destination Name
    if (strlen($dest)>2) {
        $GETLISTSQLDEST="SELECT`Name`,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where loc like '$dest'  limit 1";
        $resultdest=mysqli_query($db, $GETLISTSQLDEST);
        while ($rowdest = mysqli_fetch_assoc($resultdest)) {
            $dest=$rowdest["Name"];
            $destemail=$rowdest["ILL Email"];
        }
    } else {
        $dest="Error No Library Selected";
    }
    if ($rowtype & 1) {
        $rowclass="odd";
    } else {
        $rowclass="even";
    }
    $displaynotes=build_notes($reqnote, $lendnote);
    $dispalyreturnnotes=build_return_notes($returnnote, $returnmethodtxt);
    $displayrenewnotes= build_renewnotes($renewNote, $renewNoteLender);
    echo "<TR class='$rowclass'><TD>$illNUB</TD><TD>$title</br><i>$author</i></TD><TD>$itype</TD><TD>$needby</TD><TD><a href='mailto:".$destemail."?Subject=NOTE Request ILL# ".$illNUB."' >$dest</a></TD><TD>$reqp</TD><TD>$duedate<br>$shiptxt</TD><TD>$timestamp</TD><TD>$statustxt</TD>";
    if ($fill == 3) {
        #Only show cancel button if request has not been answered
        echo "<TD><a href ='/cancel?num=$illNUB&a=3'>Cancel Request</a></TD></TR> ";
    } elseif (($fill== 1)&&(strlen($receiveaccount)<2)) {
        #Only show the recvied button of the request was filled to start with
        echo"<td><a href ='/status?num=$illNUB&a=1'>Received Item</a></td></tr> ";
    } elseif (($fill== 1)&&(strlen($receiveaccount)>1)&&(strlen($returnaccount)<1)) {
        #Only show renew and return if request was recived but not returned
        echo"<td><a href ='/renew?num=".$illNUB."&a=3'>Request a Renewal</a><br><br><a href ='/status?num=".$illNUB."&a=2'>Return Item</a></td></tr> ";
    } elseif (($fill== 1)&&(strlen($receiveaccount)>1)&&($renewAnswer!=0)&&(strlen($returnaccount)<1)) {
        #Only show renew and return if request was recived but not returned
        echo"<td><a href ='/status?num=".$illNUB."&a=2'>Return Item</a></td></tr> ";
    } else {
        echo "<td>&nbsp</td>";
    }

    if ((strlen($reqnote) > 2) || (strlen($lendnote) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displaynotes</TD></TR>";
    }
    if ((strlen($returnnote) > 2) || (strlen($returnmethod) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$dispalyreturnnotes</TD></TR>";
    }
    if ((strlen($renewNote) > 2) || (strlen($renewNoteLender) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displayrenewnotes</TD></TR>";
    }



    $rowtype = $rowtype + 1;
}
echo "</table>";
