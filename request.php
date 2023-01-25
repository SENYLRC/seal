<script>
(function($) {

Drupal.behaviors.DisableInputEnter = {
  attach: function(context, settings) {
    $('input', context).once('disable-input-enter', function() {
      $(this).keypress(function(e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }requesterEMAIL
      });
    });
  }
}
})(jQuery);
</script>
<p>Please review the details of your request and then select a library to send your request to.</p>
<form action="sent" method="post">
<?php
####Define the different library systems
##Systems using OPALS
$DCschoolsystem="Dutchess BOCES School Library System";
$RCschoolsystem="Rockland School Library System";
$OUschoolsystem="Orange-Ulster School Library System";
$Uschoolsystem="Ulster School Library System";

#Systems using TLC
$SUschoolsystem="Sullivan School Library System";

##Systems using Sirsi
$RCLSpublicsystem="Ramapo-Catskill Library System";
$dominicancollege="Dominican College";
$nystatelibrary="New York State Library";

##Innovative Interfaces, Catalogs
$MHLSpublicsystem="Mid-Hudson Library System";
$MSMCcatalog="Mount St. Mary College";
$VCcatalog="Vassar College";
$BARDcollege="Bard College";
$ADELPHI="Adelphi University - Hudson Valley Center";
$WESTPOINT="United States Military Academy at West Point";

#Worldshare
$CULINARY="Culinary Institute of America";

#Koha Catalogs
$SSESLCfdr="Franklin D. Roosevelt Library";
$SSESLC="SENYLRC Special Library Catalog";
$SSESLCcary="Cary Institute";
$SSESLCnki="Nathan Kline Institute";

#SUNY Colleges using Ex Libris
$SUNYR="Rockland Community College";
$SUNYCG="Columbia-Greene Community College";
$SUNYS="Sullivan County Community College";
$SUNYNP="SUNY New Paltz";
$SUNYD="Dutchess Community College";
$SUNYO="Orange County Community College";
$SUNYU="Ulster County Community College";

#Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];

########Function to see if requester and destination are part of same system###########################
function checkfilter($libsystem, $profilesystem)
{
    if ($profilesystem==$libsystem) {
        $filtervalue='1';
    } else {
        $filtervalue='0';
    }
    return $filtervalue;
}
#########################################################################################There are two item type functions, one for MHLS and one for everyone else##########################################################################
####Fuction to add color for certian available status############33
function setavailColor($mylocalAvailability)
{
    if ((strpos($mylocalAvailability, 'CHECKED IN')!==false)||(strpos($mylocalAvailability, 'AVAILABLE')!==false)||(strpos($mylocalAvailability, 'Available')!==false)) {
        $itemcolor='004200';
    } elseif ((strpos($mylocalAvailability, 'IN LIBRARY USE')!==false)||(strpos($mylocalAvailability, 'DUE')!==false)||(strpos($mylocalAvailability, 'HOLD')!==false)||(strpos($mylocalAvailability, '/')!==false)||(strpos($mylocalAvailability, 'NOT AVAILABLE')!==false)) {
        $itemcolor='800000';
    } else {
        $itemcolor='';
    }
    return $itemcolor;
}
#######Function to see if item is available for loan###############################
function checkitype($mylocholding, $itemtype)
{
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT book,av,journal,reference,ebook,ejournal FROM `SENYLRC-SEAL2-Library-Data` where alias = '$mylocholding'  limit 1";
    #echo "zack $GETLISTSQL";
    echo "zack $itemtype";

    //echo  strpos($itemtype, 'journal');
    //echo  strcmp($itemtype, 'journal (electronic)');
    $result=mysqli_query($db, $GETLISTSQL);
    $row = $result->fetch_assoc();
    #this line is only for offline testing
    #$row = array('book' => 0, 'av' => 1, 'journal' => 1, 'reference'=>0, 'electronic'=>1);
    if (strpos($mylocholding, 'New York State Library')!== false) {
        #allow all items for the NY State Library at their request
        return 1;
    }
    echo "zack";
    if ((strpos($itemtype, 'book') !== false)||(strpos($itemtype, 'map') !== false)||(strpos($itemtype, 'other') !== false)) {
        if (($row['book']==1)&&(strpos($itemtype, 'elec') == false)) {
            #Checking if book is allowed
            return 1;
        }
    }
    if ((strpos($itemtype, 'recording') !== false)||(strpos($itemtype, 'video') !== false) ||(strpos($itemtype, 'music') !== false) ||(strpos($itemtype, 'audio') !== false)) {
        if ($row['av']==1) {
            #Checking if book is allowed
            return 1;
        }
    }
    if ((strcmp($itemtype, 'journal') == 0)) {
        if (($row['journal']==1)&&(strpos($itemtype, 'journal') == false)) {
            #Checking if journal is allowed
            return 1;
        }
    }
    if ((strpos($itemtype, 'reference') !== false)) {
        if (($row['reference']==1)&&(strpos($itemtype, 'reference') == false)) {
            #Checking if reference is allowed
            return 1;
        }
    }
    if ((strpos($itemtype, 'microform') !== false)) {
        if (($row['reference']==1)) {
            #Checking if microform  is allowed using the reference setting
            return 1;
        }
    }

    #make sure to do e journals first before other e stuff
    if ((strcmp($itemtype, 'journal (electronic)') == 0)) {
        echo "zack here";
        if (($row['ejournal']==1)) {
            #Checking if e journal or journals is allowed
            return 1;
        }
    }
    if ((strcmp($itemtype, 'electronic') == 0)) {
        if (($row['ebook']==1)) {
            #Checking if e books or journals is allowed
            return 1;
        }
    }
    return 0;
}


#######Function to see if item is available for loan MHLS Library###############################
function checkitypeMHLS($mylocholding, $itemtype)
{
    #Remote parentheses if they are in the item type
    $itemtype=str_replace(array( '(', ')' ), '', $itemtype);
    #Remove whitespaces
    $itemtype = str_replace(' ', '', $itemtype);
    # This was used specifically by the SUNY Campus
    if (strcmp($itemtype, 'book electronic') == 0) {
        $itemtype='electronic';
    }
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT book,av,journal,reference,ebook,ejournal FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%'  limit 1";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = $result->fetch_assoc();
    #this line is only for offline testing
    #$row = array('book' => 0, 'av' => 1, 'journal' => 1, 'reference'=>0, 'electronic'=>1);
    if ((strpos($itemtype, 'book') !== false)||(strpos($itemtype, 'map') !== false)||(strpos($itemtype, 'other') !== false)) {
        if (($row['book']==1)&&(strpos($itemtype, 'elec') == false)) {
            #Checking if book is allowed
            return 1;
        }
    }
    if ((strpos($itemtype, 'recording') !== false)||(strpos($itemtype, 'video') !== false) ||(strpos($itemtype, 'music') !== false) ||(strpos($itemtype, 'audio') !== false)) {
        if ($row['av']==1) {
            #Checking if book is allowed
            return 1;
        }
    }
    if ((strcmp($itemtype, 'journal') == 0)) {
        if (($row['journal']==1)&&(strpos($itemtype, 'journal') == false)) {
            #Checking if journal is allowed
            return 1;
        }
    }
    if ((strpos($itemtype, 'reference') !== false)) {
        if (($row['reference']==1)&&(strpos($itemtype, 'reference') == false)) {
            #Checking if reference  if ((strcmp($itemtype, 'journal (electronic)') == 0)) { is allowed
            return 1;
        }
    }
    #make sure to do e journals first before other e stuff
    if ((strcmp($itemtype, 'journal (electronic)') == 0)) {
        if (($row['ejournal']==1)) {
            #Checking if e journal or journals is allowed
            return 1;
        }
    }
    if ((strcmp($itemtype, 'electronic') == 0)) {
        if (($row['ebook']==1)) {
            #Checking if e books or journals is allowed
            return 1;
        }
    }
    return 0;
}


#####################################################################################################################################
####Function to see if library is  part of SEAL##############
function checklib_ill($mylocholding)
{
    $libparticipant='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT loc,participant,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where alias = '$mylocholding' ";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libparticipant = $row;
    return $libparticipant;
}

####Function to see if MHLS library is  part of SEAL##############
function checklib_illMHLS($mylocholding)
{
    $libparticipant='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT loc,participant,`ILL Email` FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libparticipant = $row;
    return $libparticipant;
}

####Function to translate library name from alias to real name##############
function getlibname($mylocholding)
{
    $libname='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT name FROM `SENYLRC-SEAL2-Library-Data` where  alias = '$mylocholding'";
    # echo "<!--  ".  $GETLISTSQL ." -->";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    echo "<!--";
    print_r($row);
    echo "-->";
    $libname = $row[0];
    #$libname[1] = $GETLISTSQL;
    #echo "<!--".$libname."zack-->";
    return $libname;
}

####Function to translate MHLS library name from alias to real name##############
function getlibnameMHLS($mylocholding)
{
    $libname='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT name FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' limit 1 ";
    #   echo "<!--  ".  $GETLISTSQL ." -->";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libname = $row[0];
    #  echo "<!--".$libname."zack-->";
    return $libname;
}


####Function to get lib system ID ##############
function getlibsystem($mylocholding)
{
    $libsystemq='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT system FROM `SENYLRC-SEAL2-Library-Data` where alias = '$mylocholding' limit 1";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libsystemq = $row[0];

    return $libsystemq;
}

####Function to get lib system IDfor MHLS ##############
function getlibsystemMHLS($mylocholding)
{
    $libsystemq='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT system FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' limit 1";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libsystemq = $row[0];

    return $libsystemq;
}

####Function to see if library is syspended##############
function checklib_suspend($mylocholding)
{
    $libparticipant='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT suspend FROM `SENYLRC-SEAL2-Library-Data` where alias = '$mylocholding'";
    # echo "<!--  ".  $GETLISTSQL ." -->";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libparticipant = $row[0];
    return $libparticipant;
}

####Function to see if library is syspended for MHLS##############
function checklib_suspendMHLS($mylocholding)
{
    $libparticipant='';
    require '../seal_script/seal_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT suspend FROM `SENYLRC-SEAL2-Library-Data` where alias like '%$mylocholding%' ";
    #echo "<!--  ".  $GETLISTSQL ." -->";
    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libparticipant = $row[0];
    return $libparticipant;
}

#######This function is used for the encoding of the curl command
function myUrlEncode($string)
{
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

####Define the server to make the CURL request to
$reqserverurl='https://senylrc.indexdata.com/service-proxy/?command=record\\&windowid=';
###Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);

######put in curl coammd in as html comment for development
 echo "<!-- my cmd is  $cmd \n-->";

####Run the CURL to get XML data
$output = shell_exec($cmd);

#####/put xml in html src for development
#echo "<!-- \n";
#print_r ($output);
#echo "\n-->\n\n";

####Pull the information of the person making the request from Drupal users
global $user;   // load the user entity so to pick the field from.

$user_contaning_field = user_load($user->uid);  // Check if we're dealing with an authenticated user

if ($user->uid) {    // Get field value;
    $field_first_name = field_get_items('user', $user_contaning_field, 'field_first_name');
    $field_last_name = field_get_items('user', $user_contaning_field, 'field_last_name');
    $field_your_institution = field_get_items('user', $user_contaning_field, 'field_your_institution');
    $field_loc_location_code = field_get_items('user', $user_contaning_field, 'field_loc_location_code');
    $field_street_address =   field_get_items('user', $user_contaning_field, 'field_street_address');
    $field_city_state_zip =   field_get_items('user', $user_contaning_field, 'field_city_state_zip');
    $field_work_phone =   field_get_items('user', $user_contaning_field, 'field_work_phone');
    $field_home_library_system =   field_get_items('user', $user_contaning_field, 'field_home_library_system');
    $field_filter_own_system =   field_get_items('user', $user_contaning_field, 'field_filter_own_system');
    $email = $user->mail;
    $field_backup_email =  field_get_items('user', $user_contaning_field, 'field_backup_email');
}

//Get the value of the array and set to $backupemail
$backupemail=$field_backup_email[0]['value'];
//check if the field_backup_email is a valid Email

if (filter_var($backupemail, FILTER_VALIDATE_EMAIL)) {
    //valid address do nothing;
} else {
    //not valid address unset the variable
    unset($backupemail);
}

//check if backup email is set
if (isset($backupemail)) {
    // Use == operator
    if ($email == $backupemail) {
        //email and backup are the same, do nothing
    } else {
        //email and backup are different add backup to the request
        $email =$backupemail.','.$email;
    }
}
########Display the details of the person making the request
echo "<h1>Requester Details</h1>";
echo "First Name:  " .$field_first_name[0]['value']. "<br>";
echo "Last Name:  ".$field_last_name[0]['value']. "<Br>";
echo "E-mail:  ".$email. "<br>";
echo  "Institution:  ".$field_your_institution[0]['value'] ."<br>";
echo    "Work Phone: ".$field_work_phone[0]['value'] ."<br>";
echo   "Mailing Address:<br>  ".$field_street_address[0]['value'] ."<br> ".$field_city_state_zip[0]['value'] ."<br><br>";
echo "<input type='hidden' name='fname' value= ' ".$field_first_name[0]['value'] ." '>";
echo "<input type='hidden' name='lname' value= ' ".$field_last_name[0]['value'] ." '>";
echo "<input type='hidden' name='email' value= ' ".$email ."'>";
$field_your_institution_clean=htmlspecialchars($field_your_institution[0]['value'], ENT_QUOTES);
echo "<input type='hidden' name='inst' value= ' ".$field_your_institution_clean." '>";
echo "<input type='hidden' name='address' value= ' ".$field_street_address[0]['value'] ." '>";
echo "<input type='hidden' name='caddress' value= ' ".$field_city_state_zip[0]['value'] ." '>";
echo "<input type='hidden' name='wphone' value= ' ".$field_work_phone[0]['value'] ." '>";
echo "<input type='hidden' name='reqLOCcode' value= ' ".$field_loc_location_code[0]['value'] ." '>";
#Display the request form to user
?>
<hr>
Need by date <input type="text" name="needbydate"><br>
Note <input type="text" size="100" name="reqnote"><br><br>
Is this a request for an article?
Yes <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="yesCheck">
No <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="noCheck"><br>
<div id="ifYes" style="display:none">
Article Title: <input size="80" type="text" name="arttile"><br>
Article Author: <input size="80" type='text' name='artauthor'><br>
Volume: <input size="80" type='text' name='artvolume'><br>
Issue:  <input type='text' name='artissue'><br>
Pages: <input type='text' name='artpage' ><br>
Issue Month: <input type='text' name='artmonth' ><br>
Issue Year: <input type='text' name='artyear' ><br>
Copyright compliance:  <select name="artcopyright">  <option value=""></option> <option value="ccl">CCL</option>   <option value="ccg">CCG</option>  </select>
</div><br><hr>
<?php


//XML file for request for development
#$file = 'https://seal2.senylrc.org/zackwork/output.xml';
//load test file from server
//$records = new SimpleXMLElement($file, null, true); //for testing

#Now we process the xml for Indexdata
$records = new SimpleXMLElement($output); // for production
$requestedtitle=$records->{'md-title-complete'};
$requestedtitle2=$records->{'md-title-number-section'};
$requestedauthor=$records->{'md-author'};
$requested=$records->{'md-title'};
$itemtype=$records->{'md-medium'};
#Remove any white space stored in item type
$itemtype=trim($itemtype);
$pubdate=$records->{'md-date'};
$isbn=$records->{'md-isbn'};
$issn=$records->location->{'md-issn'};

$requestedauthor = preg_replace('/[[:^print:]]/', '', $requestedauthor);
$requestedtitle = preg_replace('/[[:^print:]]/', '', $requestedtitle);
$requestedtitle2 = preg_replace('/[[:^print:]]/', '', $requestedtitle2);
echo "Requested Title:<b>: " . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "Requested Author:<b>: " . $requestedauthor ."</b><br>";
echo "Item Type:  " . $itemtype."<br>";
echo "Publication Date: " . $pubdate."<br>";
if (strlen($issn)>0) {
    echo "ISSN: " . $issn."<br>";
}
if (strlen($isbn)>0) {
    echo "ISBN: " . $isbn."<br>";
}
echo "<br>";
#Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor, ENT_QUOTES);



echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";

########Pull holding info and make available to requester to choose one#################################
###Set receiver email to senylrc for testing
#$destemail="noc@senylrc.org";

##This will loop through all the libraries that have title and see if they should be in drop down to a make a request
echo "<select required name='destination'>";
echo "<option style='background-color:#d4d4d4;'  value=''> Please Select a library</option>";
#This variable is used to count destination libraries available to make the request
$loccount='';
foreach ($records->location as $location) {

####Set to the locname to the current location node in xml response##################
    $locname = $location['name'];

    if ($locname == $ADELPHI) {
        #This is for Adelphi which has a special filter
        foreach ($location->holdings->holding as $holding) {
            $mylocholding=$locname;
            $mylocalcallNumber=$holding->callNumber;
            $mylocalAvailability=$holding->localAvailability;
            $mylocallocation=$holding->localLocation;
            #Remove colon from call numbers
            $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);

            #remove single quote from call numbers
            $mylocalcallNumber =htmlspecialchars($mylocalcallNumber, ENT_QUOTES);

            #####See if it is in Hudson Valley
            if ((strcmp($mylocallocation, "Hudson Valley Reserve") == 0) ||(strcmp($mylocallocation, "Hudson Valley") == 0)) {
                ##############See if holding is from a SEAL Library and get email
                $sealcheck=checklib_ill($mylocholding);
                $destloc=$sealcheck[0];
                $destemail=$sealcheck[2];
                $sealstatus=$sealcheck[1];
                ######Check if they will loan that item type
                $itemtypecheck = checkitype($mylocholding, $itemtype);
                ################See if library is suspended#####################
                $suspendstatus=checklib_suspend($mylocholding);
                ########Translate library alias to get libsystem
                $libsystemq=getlibsystem($mylocholding);
                #########################See if we need to filter library for requester#################
                if ($field_filter_own_system[0]['value']>0) {
                    $filterstatus=checkfilter("", $field_home_library_system[0]['value']);
                } else {
                    $filterstatus=0;
                }
                #######Set Libname from XML data
                $libname=$locname;
                ####If we don't have a real name in database use the library alias from the XML data
                #####Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
                if (($suspendstatus==0)&&($sealstatus==1)&&($itemtypecheck==1)&&($filterstatus==0)&&(strlen($destemail) > 2)) {
                    $loccount=$loccount+1;
                    echo"<option value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>". q."</strong> Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
                }
            }
        }#end for loop for Adelphi
    } elseif (($locname == $DCschoolsystem) || ($locname == $RCschoolsystem)  ||   ($locname == $Uschoolsystem)  ||   ($locname == $OUschoolsystem)) {
        #####Pull the checksum for the location
        $schoolchecksum=$location['checksum'];
        #####################redo the curl statement to includes the checksum
        $cmdschool= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$schoolchecksum\&offset=1";
        ######This echo will show the CURL statment as an HTML comment
        #echo "<!-- my cmd school is $cmdschool \n-->";
        $outputschool = shell_exec($cmdschool);
        $recordssSCHOOL = new SimpleXMLElement($outputschool); // for production
        #print_r($recordssSCHOOL);
        ######Go through the holding records
        foreach ($recordssSCHOOL->d852 as $d852) {
            $schoolavil=$d852['i1'];
            $schoolloc=$d852->sb;
            $schoolcall1=$d852->sh;
            #Remove colon from call numbers
            $schoolcall1= str_replace(':', '.', $schoolcall1);
            $schoolcall1 =htmlspecialchars($schoolcall1, ENT_QUOTES);
            ##############See if holding is from a SEAL Library and get email
            $sealcheck=checklib_ill($schoolloc);
            $destloc=$sealcheck[0];
            $destemail=$sealcheck[2];
            $sealstatus=$sealcheck[1];
            ################See if library is suspended#####################
            $suspendstatus=checklib_suspend($schoolloc);
            ######Check if they will loan that item type
            $itemtypecheck = checkitype($schoolloc, $itemtype);
            #echo "<!-- My loc location:". $schoolloc."destloc:".$destloc." destemail: ". $destemail."suspend status".$suspendstatus."item check:".$itemtypecheck." seal status:".$sealstatus ." \n-->";
            if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail)) > 2) {
                #only process a library if they particate in seal and have a lending email
                #########Translates values to txt for patron on item  status
                if ($schoolavil>0) {
                    $schooltxtavail="Not Available";
                    $itemcolor='800000';
                } else {
                    $schooltxtavail="Available";
                    $itemcolor='004200';
                }
                ########Translate library alias to a real name for patron
                $libname=getlibname($schoolloc);

                ########Translate library alias to get libsystem
                $libsystemq=getlibsystem($schoolloc);
                #######Set Libname from XML data
                $libname=$libname;
                $libname =htmlspecialchars($libname, ENT_QUOTES);
                ####If we don't have a real name in database use the libary alias from the XML data
                if (strlen($libname) <2) {
                    $libname=$schoolloc;
                }
                #####Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
                $loccount=$loccount+1;
                $mylocalcallLocation='';
                $schoolcall1= preg_replace('/[:]/', ' ', $schoolcall1);
                echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $schoolloc .":".$libname.":".$libsystemq.":".$schooltxtavail.":".$schoolcall1.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong>   Availability: $schooltxtavail Call Number:$schoolcall1  </option>";
            }
        }#End looping through each of the school locations
    } elseif ($locname == $RCLSpublicsystem) {
        #This is for RCLS Because the status has to be translated for user
        foreach ($location->holdings->holding as $holding) {
            $mylocholding=$holding->localLocation;
            $mylocalcallNumber=$holding->callNumber;
            $mylocalAvailability=$holding->localAvailability;
            #Remove colon from call numbers
            $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);
            #remove single quote from call numbers
            $mylocalcallNumber =htmlspecialchars($mylocalcallNumber, ENT_QUOTES);
            #######Translate the - in the RCLS catalog to txt
            $mylocalAvailability=  str_replace("-", "CHECKED IN", $mylocalAvailability);
            ########Translate library alias to a real name for patron
            $libname=getlibname($mylocholding);
            ########Translate library alias to get libsystem
            $libsystemq=getlibsystem($mylocholding);
            ##############See if holding is from a SEAL Library and get email
            $sealcheck=checklib_ill($mylocholding);
            $destloc=$sealcheck[0];
            $destemail=$sealcheck[2];
            $sealstatus=$sealcheck[1];
            ######Check if they will loan that item type
            $itemtypecheck = checkitype($mylocholding, $itemtype);
            ################See if library is suspended#####################
            $suspendstatus=checklib_suspend($mylocholding);
            #echo "<!-- My loc location:". $mylocholding."destloc:".$destloc." destemail: ". $destemail."suspend status".$suspendstatus."item check:".$itemtypecheck." seal status:".$sealstatus ." \n-->";

            if (($suspendstatus==0)&&($sealstatus==1)&&($itemtypecheck==1)&&(strlen($destemail)) > 2) {

      #########################See if we need to filter library for requester#################
                if ($field_filter_own_system[0]['value']>0) {
                    $profilesystem=$field_home_library_system[0]['value'];
                    $filterstatus=checkfilter("RC", $profilesystem);
                } else {
                    $filterstatus=0;
                }
                #######Set Libname from XML data
                $libname=$libname;
                $libname =htmlspecialchars($libname, ENT_QUOTES);
                $itemcolor=setavailColor($mylocalAvailability);
                ####If we don't have a real name in database use the libary alias from the XML data
                if (strlen($libname) <2) {
                    $libname= $mylocholding;
                }
                #####Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
                if ($filterstatus==0) {
                    $loccount=$loccount+1;
                    $mylocalcallLocation='';
                    echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc." '>Library:<strong>".$libname."</strong>  [RCLS]  Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option> ";
                }
            }
        }#end for loop for rcls
    } elseif ($locname == $MHLSpublicsystem) {
        foreach ($location->holdings->holding as $holding) {
            $mylocholding=$holding->localLocation;
            $mylocalcallNumber=$holding->callNumber;
            $mylocalAvailability=$holding->localAvailability;
            #Remove colon from call numbers
            $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);
            #This is used for the college folks more often
            $mylocalcallLocation=$holding->shelvingLocation;
            #Have to do this for the those who put quotes in the call number
            $mylocalcallNumber=htmlspecialchars($mylocalcallNumber, ENT_QUOTES);
            ##############See if holding is from a SEAL Library and get email
            $sealcheck=checklib_illMHLS($mylocholding);
            $destloc=$sealcheck[0];
            $destemail=$sealcheck[2];
            $sealstatus=$sealcheck[1];
            ################See if library is suspended#####################
            $suspendstatus=checklib_suspendMHLS($mylocholding);
            ######Check if they will loan that item type
            $itemtypecheck = checkitypeMHLS($mylocholding, $itemtype);
            if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail)) > 2) {

      #only process a library if they particate in seal and have a lending email

                #Translate alias to a human readable library name
                $libname=getlibnameMHLS($mylocholding);
                $libname=$libname;
                $libname =htmlspecialchars($libname, ENT_QUOTES);
                ########Get the Library system for the destination library
                $libsystemq=getlibsystemMHLS($mylocholding);
                $itemcolor=setavailColor($mylocalAvailability);
                #########################See if we need to filter library for requester from MH requesters#################
                if ($field_filter_own_system[0]['value']>0) {
                    $filterstatus=checkfilter("MH", $field_home_library_system[0]['value']);
                } else {
                    $filterstatus=0;
                }
                #####If they are not filtering own system show this library as a destination
                if ($filterstatus==0) {
                    $loccount=$loccount+1;
                    echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong> [MHLS] Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
                }
            }
        }##This end the foreach statement for the MHLS catalogs
    ########This is for the Dominican College
    } elseif (($locname == $dominicancollege) || ($locname ==$nystatelibrary)) {
        foreach ($location->holdings->holding as $holding) {
            $mylocholding=$locname;
            $mylocalcallNumber=$holding->callNumber;
            $mylocalAvailability=$holding->localAvailability;
            $mylocalcallLocation=$holding->localLocation;
            #Remove colon from call numbers
            $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);
            #remove single quote from call numbers
            $mylocalcallNumber =htmlspecialchars($mylocalcallNumber, ENT_QUOTES);
            #######Translate the - in the Dominican catalog to txt
            $mylocalAvailability=  str_replace("-", "CHECKED IN", $mylocalAvailability);
            ########Translate library alias to a real name for patron
            $libname=$locname;
            $itemcolor=setavailColor($mylocalAvailability);
            ########Translate library alias to get libsystem
            $libsystemq=getlibsystem($mylocholding);
            ##############See if holding is from a SEAL Library and get email
            $sealcheck=checklib_ill($mylocholding);
            $destloc=$sealcheck[0];
            $destemail=$sealcheck[2];
            $sealstatus=$sealcheck[1];
            ################See if library is suspended#####################
            $suspendstatus=checklib_suspend($mylocholding);
            ######Check if they will loan that item type
            $itemtypecheck = checkitype($mylocholding, $itemtype);
            #####Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
            if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1) &&(strlen($destemail) > 2)) {
                $loccount=$loccount+1;
                echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc." '>Library:<strong>".$libname."</strong>   Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option> ";
            }
        }
        #########This is for the Koha catalog hosted at SENYLRC
    } elseif (($locname == $SSESLC) ||  ($locname == $SSESLCfdr)  ||  ($locname == $SSESLCcary)||  ($locname == $SSESLCnki)) {
        #####Pull the checksum for the location
        $seslcchecksum=$location['checksum'];
        #####################redo the curl statement to includes the checksum
        $cmdseslc= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$seslcchecksum\&offset=1";
        $outputseslc = shell_exec($cmdseslc);
        ######This echo will show the CURL statment as an HTML comment
    echo "<!-- my cmd school is $cmdseslc \n-->";
    $recordssSESLC= new SimpleXMLElement($outputseslc); // for production
    ######Go through the holding records
    foreach ($recordssSESLC->d952 as $d952) {
        $seslcavil=$d952['i1'];
        $seslcloc=$d952->sb;
        $seslccall=$d952->s6;
        $loanstatus=$d952->s7;
        #Remove colon from call numbers
        $seslccall= str_replace(':', '.', $seslccall);
        #########Translates values to txt for patron on item  status
        if (($seslcavil>0) || ($loanstatus>0)) {
            $seslcavil="Not Available";
            $itemcolor='800000';
        } else {
            $seslcavil="Available";
            $itemcolor='004200';
        }
        ########Translate library alias to a real name for patron
        $libname=getlibname($seslcloc);
        ########Translate library alias to system id
        $libsystemq=getlibsystem($seslcloc);
        ##############See if holding is from a SEAL Library and get email
        $sealcheck=checklib_ill($seslcloc);
        $destloc=$sealcheck[0];
        $destemail=$sealcheck[2];
        $sealstatus=$sealcheck[1];
        ######Check if they will loan that item type
        $itemtypecheck = checkitype($seslcloc, $itemtype);
        ################See if library is suspended#####################
        $suspendstatus=checklib_suspend($seslcloc);
        if (($suspendstatus==0)&&($itemtypecheck==1)&&($sealstatus==1)&&(strlen($destemail) > 2)) {
            #######Set Libname from XML data
            $libname=$libname;
            $libname =htmlspecialchars($libname, ENT_QUOTES);
            ####If we don't have a real name in database use the libary alias from the XML data
            if (strlen($libname) <2) {
                $libname= $seslcloc;
            }

            #####Show this option to patron if SEAL Status is 1 and Suspendstatus is 0
            $loccount=$loccount+1;
            $mylocalcallLocation='';
            echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $seslcloc.":".$libname.":".$libsystemq.":".$seslcavil.":".$seslccall.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong>   Availability: $seslcavil  Call Number:  $seslccall </option>";
        }
    }#end looping through Koha records
    } elseif (($locname == $SUNYR) || ($locname == $SUNYCG)  || ($locname == $SUNYS) || ($locname == $SUNYNP) || ($locname == $SUNYU) || ($locname == $SUNYO) || ($locname == $SUNYD)) {
        if ((strpos($itemtype, 'journal (electronic)') !== false)) {
            foreach ($records->location->holdings as $ejrnlocation) {
                $libname=$locname;
                $mylocalcallNumber="Online";
                $mylocalAvailability="Unknow";
                ##############See if holding is from a SEAL Library and get email
                $sealcheck=checklib_ill($libname);

                $destloc=$sealcheck[0];
                $destemail=$sealcheck[2];
                $sealstatus=$sealcheck[1];
                if (strlen($mylocholding<2)) {
                    $mylocholding=none;
                }
                ################See if library is suspended#####################
                $suspendstatus=checklib_suspend($libname);
                ######Check if they will loan that item type

                $itemtypecheck = checkitype($libname, $itemtype);
                if (($sealstatus==1)&&($itemtypecheck==1)&& (strlen($destemail) > 2)&& ($suspendstatus==0)) {
                    #only process a library if they particate in seal and have a lending email
                    ########Get the Library system for the destination library
                    $libsystemq=getlibsystem($libname);
                    $loccount=$loccount+1;
                    echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong> Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
                }#End porccesing destination library that is active in SEAL
            }
        } else {
            foreach ($location->holdings->holding as $holding) {
                $mylocholding=$holding->localLocation;
                $mylocalcallNumber=$holding->callNumber;
                $mylocalAvailability=$holding->localAvailability;
                #Remove colon from call numbers
                $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);
                #This is used for the college folks more often
                $mylocalcallLocation=$holding->shelvingLocation;
                #Have to do this for the those who put quotes in the call number
                $mylocalcallNumber=htmlspecialchars($mylocalcallNumber, ENT_QUOTES);
                #Set the variable libname to the SUNY Name
                $libname=$locname;
                #######Translate the - in the New Paltz catalog to txt
                $mylocalAvailability=  str_replace("-", "NOT AVAILABLE", $mylocalAvailability);
                $itemcolor=setavailColor($mylocalAvailability);
                ##############See if holding is from a SEAL Library and get email
                $sealcheck=checklib_ill($libname);
                $destloc=$sealcheck[0];
                $destemail=$sealcheck[2];
                $sealstatus=$sealcheck[1];
                ################See if library is suspended#####################
                $suspendstatus=checklib_suspend($libname);
                ######Check if they will loan that item type

                $itemtypecheck = checkitype($libname, $itemtype);

                if (($sealstatus==1)&&($itemtypecheck==1)&& (strlen($destemail) > 2)&& ($suspendstatus==0)) {
                    #only process a library if they particate in seal and have a lending email
                    ########Get the Library system for the destination library
                    $libsystemq=getlibsystem($libname);
                    $loccount=$loccount+1;
                    echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong> Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
                }#End porccesing destination library that is active in SEAL
            }##This end the foreach statement for the SUNY Catalogs
        }#end of item check
    } else {
        foreach ($location->holdings->holding as $holding) {
            $mylocholding=$holding->localLocation;
            $mylocalcallNumber=$holding->callNumber;
            $mylocalAvailability=$holding->localAvailability;
            #Remove colon from call numbers
            $mylocalcallNumber= str_replace(':', '.', $mylocalcallNumber);

            #This is used for the college folks more often
            $mylocalcallLocation=$holding->shelvingLocation;
            #Have to do this for the those who put quotes in the call number
            $mylocalcallNumber=htmlspecialchars($mylocalcallNumber, ENT_QUOTES);

            ##############See if holding is from a SEAL Library and get email
            $sealcheck=checklib_ill($locname);
            $destloc=$sealcheck[0];
            $destemail=$sealcheck[2];
            $sealstatus=$sealcheck[1];

            ################See if library is suspended#####################
            $suspendstatus=checklib_suspend($locname);
            ######Check if they will loan that item type
            $itemtypecheck = checkitype($locname, $itemtype);

            if (($sealstatus==1)&&($itemtypecheck==1) && (strlen($destemail) > 2)&& ($suspendstatus==0)) {
                #only process a library if they particate in seal and have a lending email
                #Set the Library name to the catalog name  this is OK for places that don't have multple locations defined
                $libname=$locname;
                $itemcolor=setavailColor($mylocalAvailability);
                ########Get the Library system for the destination library
                $libsystemq=getlibsystem($locname);

                #Set filter status to 0 since anyone in this group is not a part of the public library
                $filterstatus=0;


                #####If they are not filtering own system show this library as a destination
                if ($filterstatus==0) {
                    $loccount=$loccount+1;
                    echo"<option style='background-color:#d4d4d4;color:#".$itemcolor.";' value='". $mylocholding.":".$libname.":".$libsystemq.":".$mylocalAvailability.":".$mylocalcallNumber.":".$mylocalcallLocation.":".$destemail.":".$destloc."'>Library:<strong>".$libname."</strong> Availability: $mylocalAvailability  Call Number: $mylocalcallNumber</option>";
                }
            }#End porccesing destination library that is active in SEAL
        }##This end the foreach statement in the last else for catalogs
    }
}####This is the end of the for loop for locations
############End of looking at holdings#################################
echo "</select>";
#####If we have locations to route to show submit
if ($loccount>0) {
    echo "<input type=Submit value=Submit> ";
########If we have no locations don't show submit and display error###########
} else {
    echo "<br><br>Sorry, no available library to route your request at this time.  <a href='https://seal.senylrc.org'>Would you like to try another search ?</a>";
}
?>
</form>
