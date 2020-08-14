<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

  <script>
  $(document).ready(function() {
     $("#datepicker").datepicker();
  });
  </script>
<?php

#####See if LOC is set, if not set to 0
if (isset($_GET['loc'])){  $loc = $_GET['loc'];  }else{$loc='null';}

#####Connect to database
require '../seal_script/seal_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db,$dbname);


 if ($_SERVER['REQUEST_METHOD'] == 'POST'){
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
       $lastmodemail = $_REQUEST["lastmodemail"];
       $libname = mysqli_real_escape_string($db,$libname);
       $libemail =mysqli_real_escape_string($db,$libemail);
       $phone = mysqli_real_escape_string($db,$phone);
       $address1 =mysqli_real_escape_string($db,$address1);
       $address2 =mysqli_real_escape_string($db,$address2);
       $address3 =mysqli_real_escape_string($db,$address3);
       $oclc = mysqli_real_escape_string($db,$oclc);
       $suspend = mysqli_real_escape_string($db,$suspend);
       $book = mysqli_real_escape_string($db,$book);
       $journal = mysqli_real_escape_string($db,$journal);
       $av = mysqli_real_escape_string($db,$av);
       $ebook = mysqli_real_escape_string($db,$ebook);
       $ejournal = mysqli_real_escape_string($db,$ejournal);
       $reference = mysqli_real_escape_string($db,$reference);
        $oclc = trim($oclc);
       $libemail=trim($libemail);
       #If suspenson is set with no end date, a default one of 7 days is calulated
       if (($suspend==1)&&(strlen($enddated)<2)){
          $enddated = strtotime("+7 day");
          $enddated = date('Y-m-d', $enddated);
        }else{
           $enddated = date('Y-m-d', strtotime(str_replace('-', '/', $enddated)));
        }
       $sqlupdate = "UPDATE `$sealLIB` SET Name = '$libname',  `ILL Email` ='$libemail',suspend=$suspend,phone='$phone',address1='$address1',address2='$address2',address3='$address3',oclc='$oclc',book='$book',journal='$journal',av='$av',ebook='$ebook',ejournal='$ejournal',reference='$reference',SuspendDateEnd='$enddated',ModifyDate='$timestamp', ModEmail='$lastmodemail'  WHERE `loc` ='$loc'";
       #echo $sqlupdate;
       $result = mysqli_query($db,$sqlupdate);

       echo  "Library Had Been Edited<br><br>";
       echo "<a href='/user'>Return to My Account</a>";
   }else{
      $GETLISTSQL="SELECT * FROM  `$sealLIB` WHERE `loc` ='$loc' limit 1 ";
      $GETLIST = mysqli_query($db,$GETLISTSQL);
      $GETLISTCOUNT = '1';
      #Get Drupal account email to record user making changes
     global $user;   // load the user entity so to pick the field from.
     $email = $user->mail;

         while ($row = mysqli_fetch_assoc($GETLIST)) {
        $libname = $row["Name"];
        $libalias = $row["alias"];
        $libemail = $row["ILL Email"];
        $oclc = $row["oclc"];
        $loc = $row["loc"];
        $phone = $row["phone"];
        $address1  = $row["address1"];
        $address2  = $row["address2"];
        $address3  = $row["address3"];
        $libparticipant  = $row["participant"];
        $libsuspend  = $row["suspend"];
        $system = $row["system"];
        $book = $row["book"];
        $reference = $row["reference"];
        $av = $row["av"];
        $ebook = $row["ebook"];
        $ejournal = $row["ejournal"];
        $journal = $row["journal"];
        $enddate  = $row["SuspendDateEnd"];
        $timestamp = $row["ModifyDate"];
        $lastmodemail = $row["ModEmail"];
        $libilliad = $row["Illiad"];
        $libemailalert = $row["LibEmailAlert"];
     }
    if ($loc != 'null'){
     ?>
     <form action="/libprofile?<?php echo $_SERVER['QUERY_STRING'];?>" method="post">
     <B>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname" value="<?php echo $libname?>"><br>
     <B>Library Alias:</b> <?php echo $libalias?><br>
     <B>Library ILL Email:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libemail" value="<?php echo $libemail?>"><br>
     <B>Library Phone:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="phone" value="<?php echo $phone?>"><br>
     <B>Library Address Dept:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address1" value="<?php echo $address1?>"><br>
     <B>Library Address Street</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address2" value="<?php echo $address2?>"><br>
     <B>Library Address City and State</b> <input type="text" SIZE=60 MAXLENGTH=255  name="address3" value="<?php echo $address3?>"><br>
     <B>OCLC Symbol:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="oclc" value="<?php echo $oclc?>"><br>
     <B>LOC Location:</b> <?php echo $loc?><br>
     <B>Lib Email Alert: </b> <?php if($libemailalert=='1'){echo "Yes";}else{echo "No";}?><br>
     <B>Lib Illiad API: </b>  <?php if($libilliad=='1'){echo "Yes";}else{echo "No";}?><br>
   <?php if($system=="DU"){ $system="Dutchess BOCES";}?>
     <?php if($system=="MH"){ $system="Mid-Hudson Library System";}?>
     <?php if($system=="OU"){ $system="Orange Ulster BOCES";}?>
     <?php if($system=="RC"){ $system="Ramapo Catskill Library System";}?>
     <?php if($system=="RB"){ $system="Rockland BOCES";}?>
     <?php if($system=="SE"){ $system="SENYLRC";}?>
     <?php if($system=="SB"){ $system="Sullivan BOCES";}?>
    <B>Library System:</b> <?php echo $system?><br>
      <br>
     <B>Suspend Your Library's lending status?   </b><select name="suspend">  <option value="0" <?php if($libsuspend=="0") echo "selected=\"selected\""; ?>>No</option><option value="1" <?php if($libsuspend=="1") echo "selected=\"selected\""; ?>>Yes</option></select><br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>YES</strong> will <strong>prevent</strong> your library getting ILL requests<br>&nbsp&nbsp&nbsp&nbspSetting this to <strong>NO</strong> will <strong>allow</strong> your library to receive ILL requests.<br>
 Suspension End Date:
     <input id="datepicker" name="enddate"/>
<br><br>
     <?php
          if($libsuspend=="1") {
           echo "Your Library has <strong>enable suspension</strong> until $enddate <br>";
        }else{
            echo "Your Library has suspension disabled";
      }
      ?>

      <br><br>
      <B>Items willing to loan in SEAL</b><br>
        <table>
        <tr>
        <td><b>Print Book</b><td>
          <input type="radio" name="book" value="1" <?php if($book=="1") echo "checked"; ?>> Yes
          <input type="radio" name="book" value="0" <?php if($book=="0") echo "checked"; ?>> No <br>
        </td></tr>
        <tr>
        <td><b>Articles From Print Journals</b><td>
          <input type="radio" name="journal" value="1" <?php if($journal=="1") echo "checked"; ?>> Yes
          <input type="radio" name="journal" value="0" <?php if($journal=="0") echo "checked"; ?>> No <br>
        </td></tr>
        <tr>
        <td><b>Audio Video Materials</b><td>
          <input type="radio" name="av" value="1" <?php if($av=="1") echo "checked"; ?>> Yes
          <input type="radio" name="av" value="0" <?php if($av=="0") echo "checked"; ?>> No <br>
        </td></tr>
        <tr>
        <td><b>Reference</b><td>
          <input type="radio" name="reference" value="1" <?php if($reference=="1") echo "checked"; ?>> Yes
          <input type="radio" name="reference" value="0" <?php if($reference=="0") echo "checked"; ?>>No <br>
        </td></tr>
        <tr>
        <td><b>Electronic Book</b><td>
          <input type="radio" name="ebook" value="1" <?php if($ebook=="1") echo "checked"; ?>> Yes
          <input type="radio" name="ebook" value="0" <?php if($ebook=="0") echo "checked"; ?>> No <br>
        </td></tr>
        <tr>
        <td><b>Electronic Journal</b><td>
          <input type="radio" name="ejournal" value="1" <?php if($ejournal=="1") echo "checked"; ?>> Yes
          <input type="radio" name="ejournal" value="0" <?php if($ejournal=="0") echo "checked"; ?>> No <br>
        </td></tr>
      </table>
      <?php echo "<input type='hidden' name='loc' value= ' ".$loc ." '>";?><br><br>
      <?php echo "<input type='hidden' name='lastmodemail' value= ' ".$email ." '>";?><br><br>
<strong>Please click on Submit to save your profile<br></strong>
     <input type="submit" value="Submit">
    </form>
     <Br><Br>
      Last Modified: <?php echo "$timestamp;" ?> by <?php echo "$lastmodemail;" ?>
   <?php
    }
   }
