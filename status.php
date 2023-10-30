<?php
// status.php###

require '/var/www/seal_script/seal_function.php';
// Get values
$reqnumb=$_REQUEST["num"];
$returnnote=$_REQUEST["returnnote"];
$itemreturn=$_REQUEST["itemreturn"];
if (isset($_REQUEST['a'])) {
    $recanswer = $_REQUEST['a'];
} else {
    $recanswer='';
}
if (isset($_REQUEST['shipmethod'])) {
    $returnmethod = $_REQUEST['shipmethod'];
} else {
    $returnmethod='';
}

// Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

// Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$reqanswer = mysqli_real_escape_string($db, $reqanswer);
$returnnote = mysqli_real_escape_string($db, $returnnote);
$wholename = mysqli_real_escape_string($db, $wholename);

// Answers
// 1  is received
// 2  is for ship back
// 3 is for the lender recived item
// This is for marking the item recevied
if (($_SERVER['REQUEST_METHOD'] == 'GET')&&($recanswer=='1')) {
    $timestamp = date("Y-m-d H:i:s");
    $todaydate = date("Y-m-d");
    $sql = "UPDATE `$sealSTAT` SET `receiveTimeStamp` = '$timestamp', `receiveAccount` = '" .$wholename."', `receiveDate` = '$todaydate' WHERE `illNUB` = '$reqnumb'";
    if (mysqli_query($db, $sql)) {
        echo "ILL ".$reqnumb." has been received; <a href='". $_SERVER['HTTP_REFERER']."'>click here to go back to request history</a>";
    } else {
        echo "Was not able to receive item, please contact Southeastern of this error";
    }
    // This is for the shipping back
} elseif ($recanswer=='2') {
    if (strlen($returnmethod)<1) {
        echo "<form action=".$_SERVER['REDIRECT_URL']." method='get'>";
        echo "<input type='hidden' name='a' value= '2'>";
        echo "<input type='hidden' name='num' value= '".$reqnumb."'>";
        echo "How are you returning the item:<select name='shipmethod'>";
        echo "<option value=''></option>";
        echo "<option value='lc'>Library Courier</option>";
        echo "<option value='usps'>US Mail</option>";
        echo "<option value='upsfx'>UPS/FedEx</option>";
        echo "<option value='empire'>Empire Library Delivery</option>";
        echo "<option value='other'>Other</option></select><br><br>";
        echo "Return Notes <input type='text' size='100' name='returnnote'>";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    } else {
        $timestamp = date("Y-m-d H:i:s");
        $todaydate = date("Y-m-d");
        //will remove bib information when doing a return
        $sql = "UPDATE `$sealSTAT` SET `returnTimeStamp` = '$timestamp',`returnMethod` = '$returnmethod',`returnNote` = '$returnnote', `returnAccount` = '" .$wholename."', `returnDate` = '$todaydate', `patronnote` = '' WHERE `illNUB` = '$reqnumb'";
        if (mysqli_query($db, $sql)) {
            //echo $sql;
            echo "ILL ".$reqnumb." has been marked as being returned, <a href='/requesthistory'>click here to go back to request history</a>";
        } else {
            echo "Was not able to mark item as return, please contact Southeastern of this error";
        }
    }
} elseif ($recanswer=='3') {
    if (strlen($itemreturn)==0) {
        echo "<form action=".$_SERVER['REDIRECT_URL']." method='get'>";
        echo "<input type='hidden' name='a' value= '3'>";
        echo "<input type='hidden' name='num' value= '".$reqnumb."'>";
        echo "Do you wish to mark the item as returned?<br>";
        echo "<input type='radio' name='itemreturn' value='1' checked> Yes<br>";
        echo "<input type='radio' name='itemreturn' value='0'> No<br>";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    } else {
        $timestamp = date("Y-m-d H:i:s");
        $todaydate = date("Y-m-d");
        if ($itemreturn==1) {
            
            $sql = "UPDATE `$sealSTAT` SET `checkinTimeStamp` = '$timestamp', `checkinAccount` = '" .$wholename."'  WHERE `illNUB` = '$reqnumb'";
            if (mysqli_query($db, $sql)) {
                echo "ILL ".$reqnumb." has been marked as being returned, <a href='/lender-history'>click here to go back to lending history</a>";
            } else {
                echo "Was not able to mark item as return, please contact Southeastern of this error";
            }
        } else {
            echo "At your request the item has <strong>NOT</strong> been marked as checked in, <a href='/lender-history'>click here to go back to lending history</a";
        }
    }
} else {
    echo "Something has gone wrong, unable to change status.  Make sure to access page via the <a href='/user'>direct link in your profile</a>";
}
