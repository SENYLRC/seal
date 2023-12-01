<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
     $("#datepicker").datepicker();
  });
  </script>
<?php

require '/var/www/seal_script/seal_function.php';
//Get loc from user profile
$loc=$field_loc_location_code ;

// Connect to database
require '/var/www/seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $timestamp = date("Y-m-d H:i:s");
    $libname=$_REQUEST["libname"];
    $libemail=$_REQUEST["libemail"];
    $phone=$_REQUEST["phone"];
    $address1=$_REQUEST["address1"];
    $address2=$_REQUEST["address2"];
    $address3=$_REQUEST["address3"];
    $oclc=$_REQUEST["oclc"];
    $suspend=$_REQUEST["suspend"];
    $book=$_REQUEST["book"];
    $av=$_REQUEST["av"];
    $journal=$_REQUEST["journal"];
    $ebook=$_REQUEST["ebook"];
    $ejournal=$_REQUEST["ejournal"];
    $reference=$_REQUEST["reference"];
    $enddated = $_REQUEST["enddate"];
    $lbsyscourier = $_REQUEST["lbsyscourier"];
    $lbUSPS = $_REQUEST["lbUSPS"];
    $lbEmpire  = $_REQUEST["lbEmpire"];
    $lbCommCourier = $_REQUEST["lbCommCourier"];
    $lastmodemail = $email;
    $libname = mysqli_real_escape_string($db, $libname);
    $libemail =mysqli_real_escape_string($db, $libemail);
    $phone = mysqli_real_escape_string($db, $phone);
    $address1 =mysqli_real_escape_string($db, $address1);
    $address2 =mysqli_real_escape_string($db, $address2);
    $address3 =mysqli_real_escape_string($db, $address3);
    $oclc = mysqli_real_escape_string($db, $oclc);
    $suspend = mysqli_real_escape_string($db, $suspend);
    $book = mysqli_real_escape_string($db, $book);
    $journal = mysqli_real_escape_string($db, $journal);
    $av = mysqli_real_escape_string($db, $av);
    $ebook = mysqli_real_escape_string($db, $ebook);
    $ejournal = mysqli_real_escape_string($db, $ejournal);
    $reference = mysqli_real_escape_string($db, $reference);
    $oclc = trim($oclc);
    $libemail=trim($libemail);
    // If suspenson is set with no end date, a default one of 7 days is calulated
    if (($suspend==1)&&(strlen($enddated)<2)) {
        $enddated = strtotime("+7 day");
        $enddated = date('Y-m-d', $enddated);
    } else {
        $enddated = date('Y-m-d', strtotime(str_replace('-', '/', $enddated)));
    }
    $sqlupdate = "UPDATE `$sealLIB` SET Name = '$libname',  `ill_email` ='$libemail',suspend=$suspend,phone='$phone',address1='$address1',address2='$address2',address3='$address3',oclc='$oclc',book_loan='$book',periodical_loan='$journal',av_loan='$av',ebook_request='$ebook',ejournal_request='$ejournal',theses_loan='$reference',SuspendDateEnd='$enddated',ModifyDate='$timestamp', ModEmail='$lastmodemail', lbsyscourier='$lbsyscourier', lbUSPS='$lbUSPS', lbEmpire='$lbEmpire', lbCommCourier='$lbCommCourier'   WHERE `loc` ='$loc'";
    //for testing
    //  echo $sqlupdate;
    $result = mysqli_query($db, $sqlupdate);

    echo  "Library Had Been Edited<br><br>";
    echo "<a href='/user'>Return to My Account</a>";
} else {
    $GETLISTSQL="SELECT * FROM  `$sealLIB` WHERE `loc` ='$loc' limit 1 ";
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = '1';


    while ($row = mysqli_fetch_assoc($GETLIST)) {
        $libname = $row["Name"];
        $libalias = $row["alias"];
        $libemail = $row["ill_email"];
        $oclc = $row["oclc"];
        $loc = $row["loc"];
        $phone = $row["phone"];
        $address1  = $row["address1"];
        $address2  = $row["address2"];
        $address3  = $row["address3"];
        $libparticipant  = $row["participant"];
        $libsuspend  = $row["suspend"];
        $system = $row["system"];
        $book = $row["book_loan"];
        $reference = $row["theses_loan"];
        $av = $row["av_loan"];
        $ebook = $row["ebook_request"];
        $ejournal = $row["ejournal_request"];
        $journal = $row["periodical_loan"];
        $enddate  = $row["SuspendDateEnd"];
        $timestamp = $row["ModifyDate"];
        $lastmodemail = $row["ModEmail"];
        $libilliad = $row["Illiad"];
        $libilliaddate = $row["IlliadDATE"];
        $libemailalert = $row["LibEmailAlert"];
        $lbsyscourier = $row["lbsyscourier"];
        $lbUSPS = $row["lbUSPS"];
        $lbEmpire = $row["lbEmpire"];
        $lbCommCourier = $row["lbCommCourier"];
    }
    if ($loc != 'null') {
        ?>
     <form action="/libprofile?<?php echo $_SERVER['QUERY_STRING']; ?>" method="post">
     <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname" value="<?php echo $libname?>"><br>
     <B>Library Alias:</b> <?php echo $libalias?><br>
     <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail" value="<?php echo $libemail?>"><br>
     <B>Library Phone:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="phone" value="<?php echo $phone?>"><br>
     <B>Library Address Dept:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address1" value="<?php echo $address1?>"><br>
     <B>Library Address Street:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address2" value="<?php echo $address2?>"><br>
     <B>Library Address City, State and Zip:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address3" value="<?php echo $address3?>"><br>
     <B>OCLC Symbol:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="oclc" value="<?php echo $oclc?>"><br>
     <B>LOC Location:</b> <?php echo $loc?><br>
     <B>Lib Email Alert: </b> <?php if ($libemailalert=='1') {
            echo "Yes";
} else {
                                  echo "No";
                              } ?><br>
     <B>Lib Illiad API: </b>  <?php if ($libilliad=='1') {
            echo "Yes";
} else {
                                  echo "No";
                              } ?><br>
     <b>ILLiad Due Date Days:</b> <?php echo $libilliaddate?><br>
        <?php if($system=="DU") { $system="Dutchess BOCES";
        }?>
        <?php if($system=="MH") { $system="Mid-Hudson Library System";
        }?>
        <?php if($system=="OU") { $system="Orange Ulster BOCES";
        }?>
        <?php if($system=="RC") { $system="Ramapo Catskill Library System";
        }?>
        <?php if($system=="RB") { $system="Rockland BOCES";
        }?>
        <?php if($system=="SE") { $system="SENYLRC";
        }?>
        <?php if($system=="SB") { $system="Sullivan BOCES";
        }?>
    <B>Library System:</b> <?php echo $system?><br>

      <br>
     <B>Suspend Your Library's lending status?   </b><select name="suspend">  <option value="0" <?php if ($libsuspend=="0") {
            echo "selected=\"selected\"";
} ?>>No</option><option value="1" <?php if ($libsuspend=="1") {
         echo "selected=\"selected\"";
                                                                                                } ?>>Yes</option></select><br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>YES</strong> will <strong>prevent</strong> your library from getting ILL requests<br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>NO</strong> will <strong>allow</strong> your library to receive ILL requests.<br>
 Suspension End Date:
     <input id="datepicker" name="enddate"/>
     <strong><br><br>****If no date is picked, the system will default to seven (7) days****</strong>
<br><br>
        <?php
        if ($libsuspend=="1") {
            echo "".$libname."  <strong>will not receive</strong> requests until <strong>".$enddate."</strong><br>";
        } else {
            echo "".$libname."  is <strong>currently receiving</strong> requests.<br>";
        } ?>
<br><br>
<h6>Delivery Option:</h6>

<table>
        <tr>
        <td><b>Empire Delivery</b><td>
          <input type="radio" name="lbEmpire" value="Yes" <?php if ($lbEmpire=="Yes") {
                echo "checked";
} ?>> Yes
          <input type="radio" name="lbEmpire" value="No" <?php if ($lbEmpire=="No") {
                echo "checked";
} ?>> No <br>
        </td></tr>
        <tr>
          <td><b>Public Library System Courier  (MHLS or RCLS)</b><td>
            <input type="radio" name="lbsyscourier" value="Yes" <?php if ($lbsyscourier=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="lbsyscourier" value="No" <?php if ($lbsyscourier=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
        <tr>
          <td><b>US Mail</b><td>
            <input type="radio" name="lbUSPS" value="Yes" <?php if ($lbUSPS=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="lbUSPS" value="No" <?php if ($lbUSPS=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
        <tr>
          <td><b>Commercial Courier (UPS or FEDEX)</b><td>
            <input type="radio" name="lbCommCourier" value="Yes" <?php if ($lbCommCourier=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="lbCommCourier" value="No" <?php if ($lbCommCourier=="No") {
                echo "checked";
} ?>>No <br>
          </td></tr>
       
      </table>






      <br><br>
      <h6>Items willing to loan:</h6>
        <table>
        <tr>
        <td><b>Print Book</b><td>
          <input type="radio" name="book" value="Yes" <?php if ($book=="Yes") {
                echo "checked";
} ?>> Yes
          <input type="radio" name="book" value="No" <?php if ($book=="No") {
                echo "checked";
} ?>> No <br>
        </td></tr>
        <tr>
          <td><b>Print Journal or Article</b><td>
            <input type="radio" name="journal" value="Yes" <?php if ($journal=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="journal" value="No" <?php if ($journal=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
        <tr>
          <td><b>Audio Video Materials</b><td>
            <input type="radio" name="av" value="Yes" <?php if ($av=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="av" value="No" <?php if ($av=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
        <tr>
          <td><b>Reference/Microfilm</b><td>
            <input type="radio" name="reference" value="Yes" <?php if ($reference=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="reference" value="No" <?php if ($reference=="No") {
                echo "checked";
} ?>>No <br>
          </td></tr>
        <tr>
          <td><b>Electronic Book</b><td>
            <input type="radio" name="ebook" value="Yes" <?php if ($ebook=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="ebook" value="No" <?php if ($ebook=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
        <tr>
          <td><b>Electronic Journal</b><td>
            <input type="radio" name="ejournal" value="Yes" <?php if ($ejournal=="Yes") {
                echo "checked";
} ?>> Yes
            <input type="radio" name="ejournal" value="No" <?php if ($ejournal=="No") {
                echo "checked";
} ?>> No <br>
          </td></tr>
      </table>
        <?php echo "<input type='hidden' name='loc' value= ' ".$loc ." '>"; ?><br><br>
<strong>Please click on Submit to save your profile.<br></strong>
     <input type="submit" value="Submit">
    </form>
     <Br><Br>
      Last Modified: <?php echo "$timestamp;" ?> by <?php echo "$lastmodemail;" ?>
        <?php
    }
}
