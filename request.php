<script>

(function($) {

Drupal.behaviors.DisableInputEnter = {
  attach: function(context, settings) {
    $('input', context).once('disable-input-enter', function() {
      $(this).keypress(function(e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }
      });
    });
  }
}



})(jQuery);

</script>
<?php
require '/var/www/seal_script/seal_function.php';



// Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];
// Define the server to make the CURL request to
$reqserverurl='https://senylrc.indexdata.com/service-proxy/?command=record\\&windowid=';
// Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);
// put in curl command in as html comment for development
echo "<!-- my cmd is  $cmd \n-->";
// Run the CURL to get XML data
$output = shell_exec($cmd);
// put xml in html src for development
//echo "<!-- \n";
//print_r($output);
//echo "\n-->\n\n";


echo "<p>Please review the details of your request and then select a library to send your request to.</p>";
echo "<form action='sent' method='post'>";
echo "<h3>Requester Details</h3>";
echo "<b>Name:</b>  " .$field_first_name. " ".$field_last_name. "<br>";
echo "<b>E-mail:</b> ".$email. "<br>";
echo  "<b>Institution:</b>  ".$field_your_institution ."<br>";
echo  "<b>Work Phone:</b> ".$field_work_phone ."<br>";
echo  "<b>Mailing Address:</b> <br> ".$field_street_address."<br> ".$field_city_state_zip."<br><br>";
echo "<input type='hidden' name='fname' value= ' ".$field_first_name ." '>";
echo "<input type='hidden' name='lname' value= ' ".$field_last_name ." '>";
echo "<input type='hidden' name='email' value= ' ".$email ."'>";
$field_your_institution_clean=htmlspecialchars($field_your_institution, ENT_QUOTES);
echo "<input type='hidden' name='inst' value= ' ".$field_your_institution_clean." '>";
echo "<input type='hidden' name='address' value= ' ".$field_street_address ." '>";
echo "<input type='hidden' name='address2' value= ' ".$field_street_address2 ." '>";
echo "<input type='hidden' name='caddress' value= ' ".$field_city_state_zip ." '>";
echo "<input type='hidden' name='wphone' value= ' ".$field_work_phone ." '>";
echo "<input type='hidden' name='reqLOCcode' value= ' ".$field_loc_location_code ." '>";
echo "<h3>Request Details</h3>";
echo "<b>Need by date</b> <input type='text' size='100' name='needbydate'><br><br>";
echo "<b>Note</b> <input type='text' size='100' name='reqnote'><br><br>";
echo "**Patron information is optional; please follow your local policies regarding patron privacy when making a request.<br>";
echo "<b>Patron Name or Barcode</b> <input SIZE=100 MAXLENGTH=255 type='text' size='100' name='patronnote'><br><br>";
echo "<b>Is this a request for an article?</b>  ";
echo "Yes <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='yesCheck'>";
echo "No <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='noCheck' checked='checked'><br>";
echo "<div id='ifYes' style='display:none'>";
echo "<b>Article Title:</b> <input size='80' type='text' name='arttile'><br>";
echo "<b>Article Author:</b> <input size='80' type='text' name='artauthor'><br>";
echo "<b>Volume:</b> <input size='80' type='text' name='artvolume'><br>";
echo "<b>Issue:</b>  <input type='text' name='artissue'><br>";
echo "<b>Pages:</b> <input type='text' name='artpage' ><br>";
echo "<b>Issue Month:</b> <input type='text' name='artmonth' ><br>";
echo "<b>Issue Year:</b> <input type='text' name='artyear' ><br>";
echo "<b>Copyright compliance:</b>  <select name='artcopyright'>";
echo "<option value=''></option>";
echo "<option value='ccl'>CCL</option>";
echo "<option value='ccg'>CCG</option></select></div><br>";



// Now we process the xml for Indexdata
$records = new SimpleXMLElement($output); // for production
$requestedtitle=$records->{'md-title-complete'};
$requestedtitle2=$records->{'md-title-number-section'};
$requestedauthor=$records->{'md-author'};
$requested=$records->{'md-title'};
$itemtype=$records->{'md-medium'};
// Remove any white space stored in item type
$itemtype=trim($itemtype);
$pubdate=$records->{'md-date'};
$isbn=$records->{'md-isbn'};
$issn=$records->location->{'md-issn'};

echo "<b>Requested Title: </b>" . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "<b>Requested Author: </b>" . $requestedauthor ."</b><br>";
echo "<b>Item Type:</b>  " . $itemtype."<br>";
echo "<b>Publication Date: </b>" . $pubdate."<br>";
if (strlen($issn)>0) {
    echo "<b>ISSN: </b>" . $issn."<br>";
}
if (strlen($isbn)>0) {
    echo "<b>ISBN: </b>" . $isbn."<br>";
}
echo "<br>";

// Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor, ENT_QUOTES);
// echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";

echo "<p><b>Select the library you would like to request from.</b><br>";
echo "Please limit multiple copy requests to classroom sets or book clubs.</p>";
echo "<p><b>This is a request for:</b> <br>";
echo "<input type='radio' name='singlemulti' id='singleCheck' checked='checked' onclick='javascript:multiRequest();'>";
echo "a single copy <input type='radio' name='singlemulti' id='multiCheck' onclick='javascript:multiRequest();'> multiple copies<br><p>";



$loccount='0'; // Counts available locations
$deadlibraries = array(); // Initializes the array which keeps the unavailable libraries.
foreach ($records->location as $location) { // Locations loop start
    $catalogtype = find_catalog($location['name']);
    $urlrecipe = $location->{'md-url_recipe'};
    $mdid = $location->{'md-id'};
    //echo "zack my location is ".$location['name']."<br>";
    //echo "zack my catalog type is ".$catalogtype."<br>";
    foreach ($location->holdings->holding as $holding) { // generic holding loop start
        $itemavail=$holding->localAvailability;
        // if ($catalogtype == "OPALS") {
        // $itemavail=$itemavail>0 ? $itemavail="-" : $itemavail="0";
        // echo "the OPALS itemavail is ".$itemavail."<br>";
        // } #OPALS might return (-1 through +X
        $itemavail=normalize_availability($itemavail); // 0=No, 1=Yes

        $itemavailtext=set_availability($itemavail);
        $itemcallnum=$holding->callNumber;
        $itemcallnum=htmlspecialchars($itemcallnum, ENT_QUOTES); // Sanitizes callnumbers with special characters in them
        $itemlocation=$holding->localLocation; // Gets the alias
        if ($catalogtype == "Worldcat" || $catalogtype == 'cdlc' || $catalogtype == "Millennium") {
            $itemlocation=$location['name'];
        }
        if (($catalogtype == "Innovative") || ($catalogtype == "Alma") ||  ($catalogtype == "Voyager")||($catalogtype == "Folio")|| ($catalogtype == "Symphony")|| ($catalogtype == "SirsiDynix")) {
            $itemlocation=$location['name'];
        }
        if (($catalogtype == "OPALS") || ($catalogtype == "Polaris")) {
            $itemlocation=  $holding->localLocation;
        }
        if ($catalogtype == "TLC") {
            $itemlocation=$holding->localLocation; // Gets the alias
        }
        if ($catalogtype == "SymphonyRCLS") {
            $itemlocation=$holding->localLocation; // Gets the alias
        }
        if ($catalogtype == "InnovativeMHLS") {
            $itemlocation=$holding->localLocation; // Gets the alias
        }
        $locationinfo=find_locationinfo($itemlocation, $location['name']);
        $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); // Sanitizes locations with special characters in them
        $destill=$locationinfo[0]; // Destination ILL Code
        $destpart=$locationinfo[1]; // 0=No, 1=Yes

        $destemail=$locationinfo[2]; // Destination emails
        $destsuspend=$locationinfo[3]; // 0=No, 1=Yes
        $destlibsystem=$locationinfo[4]; // Destination library system
        $destlibname=$locationinfo[5]; // Destination library name
        $destAlias=$locationinfo[6]; // Destination Alias
         // translate system code to text name
         if (strcmp($destlibsystem, 'MH')==0) {
            $destlibsystemtxt = "Mid Hudson Library System";
        }else if (strcmp($destlibsystem, 'RC')==0) {
            $destlibsystemtxt = "Ramapo Catskill Library System";
        }else if (strcmp($destlibsystem, 'SE')==0) {
            $destlibsystemtxt = "SENYLRC";
        }else if (strcmp($destlibsystem, 'DU')==0) {
            $destlibsystemtxt = "Dutchess BOCES";
        }else if (strcmp($destlibsystem, 'OU')==0) {
            $destlibsystemtxt = "Orange Ulster BOCES";
        }else if (strcmp($destlibsystem, 'RB')==0) {
            $destlibsystemtxt = "Rockland BOCES";
        }else if (strcmp($destlibsystem, 'SB')==0) {
            $destlibsystemtxt = "Sullivan BOCES";
        }else if (strcmp($destlibsystem, 'UB')==0) {
            $destlibsystemtxt = "Ulster BOCES";
        }else if (strlen($destlibsystem) <1) {
            $destlibsystemtxt = "All";
        }else{
            $destlibsystemtxt = "SENYLRC Group";
        }
        $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); // Sanitizes library names with special characters in them
        //only check item type if they are active in the ILL program
        if ($destpart==1) {
            $desttypeloan=check_itemtype($destill, $itemtype); // 0=No, 1=Yes
        }
        if (($catalogtype == "Innovative") && ($itemlocation == "ODY Folio")) {
            $desttypeloan=1;
        }
        $itemlocallocation=$itemlocation; // Needed in sent.php
        echo "<!-- \n";
        echo "catalogtype: $catalogtype \n";
        echo "itemavail: $itemavail (1) \n";
        echo "itemavailtext: $itemavailtext \n";
        echo "itemlocallocation: $itemlocallocation \n";
        echo "itemlocation: $itemlocation \n";
        echo "destill: $destill \n";
        echo "destpart: $destpart (1)\n";
        echo "destemail: $destemail \n";
        echo "destsuspend: $destsuspend (0)\n";
        echo "destlibsystem: $destlibsystem \n";
        echo "destlibname: $destlibname \n";
        echo "desttypeloan: $desttypeloan (1)\n";
        echo "failmessage: $failmessage\n";
        echo "--> \n\n";
        $destfail=0; // 0=No, 1=Yes
        if ($itemavail == 0) {
            $destfail = 1;
            $failmessage = "Material unavailable, see source ILS/LMS for details";
        }
        if ($destpart == 0) {
            $destfail = 1;
            $failmessage = "Library not particpating in CaDiLaC";
        }
        if (strlen($destemail) < 2) {
            $destfail = 1;
            $failmessage = "Library has no ILL email configured";
        }
        if (($destsuspend == 1)&&($destill!='ntr')) {
            $destfail = 2;
            $failmessage = "Library not loaning / SEAL ILL Suspend";
        }
        if ($desttypeloan == 0) {
            $destfail = 2;
            $failmessage = "Library not loaning this material type";
        }
        if (($destlibsystem == $field_home_library_system) && ($field_filter_own_system == 1)) {
            $destfail = 1;
            $failmessage = "Library a member of your system, please request through your ILS/LMS";
        }
        if ($destill == "") {
            $destfail = 1;
            $destlibname = $itemlocation;
            $destlibsystem = "Unknown";
            $failmessage = "No alias match in SEAL directory";
        }
        if ($destfail == 0) {
            $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
            $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
            $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
            echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
            echo"<div class='singlereq'><input type='radio' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
            $loccount=$loccount+1;
        } elseif ($destfail == 1) {
            //only showing error code 2
        } else {
            $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystemtxt), $failmessage</div>";
            echo "<!-- Holding location failed checks. --> \n";
        }
    } // Generic holding loop end
    //do a loop for Albany Law School
    if ($location['name']== 'Albany Law School') {
        $itemtype=$records->{'md-medium'};

        // Pull the checksum for the location
        $seslcchecksum=$location['checksum'];
        // redo the curl statement to includes the checksum
        $cmdseslc= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$seslcchecksum\&offset=1";
        $outputseslc = shell_exec($cmdseslc);
        // This echo will show the CURL statment as an HTML comment
        // echo "\n<br><!-- my cmd albay law cmd is $cmdseslc \n-->";
        $recordssSESLC= new SimpleXMLElement($outputseslc); // for production
        $itemcallnum=$recordssSESLC->d050->sa;
        // Go through the holding records
        foreach ($recordssSESLC->d994 as $d994) {
            $itemlocation=$d994->sb;
            $locationinfo=find_locationinfo($itemlocation, $location['name']);
            $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); // Sanitizes locations with special characters in them
            $destill=$locationinfo[0]; // Destination ILL Code
            $destpart=$locationinfo[1]; // 0=No, 1=Yes
            $destemail=$locationinfo[2]; // Destination emails
            $destsuspend=$locationinfo[3]; // 0=No, 1=Yes
            $destlibsystem=$locationinfo[4]; // Destination library system
            $destlibname=$locationinfo[5]; // Destination library name
            $destAlias=$locationinfo[6]; // Destination Alias
            $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); // Sanitizes library names with special characters in them
            $desttypeloan=check_itemtype($destill, $itemtype); // 0=No, 1=Yes

             // translate system code to text name
             if (strcmp($destlibsystem, 'MH')==0) {
                $destlibsystemtxt = "Mid Hudson Library System";
            }else if (strcmp($destlibsystem, 'RC')==0) {
                $destlibsystemtxt = "Ramapo Catskill Library System";
            }else if (strcmp($destlibsystem, 'SE')==0) {
                $destlibsystemtxt = "SENYLRC";
            }else if (strcmp($destlibsystem, 'DU')==0) {
                $destlibsystemtxt = "Dutchess BOCES";
            }else if (strcmp($destlibsystem, 'OU')==0) {
                $destlibsystemtxt = "Orange Ulster BOCES";
            }else if (strcmp($destlibsystem, 'RB')==0) {
                $destlibsystemtxt = "Rockland BOCES";
            }else if (strcmp($destlibsystem, 'SB')==0) {
                $destlibsystemtxt = "Sullivan BOCES";
            }else if (strcmp($destlibsystem, 'UB')==0) {
                $destlibsystemtxt = "Ulster BOCES";
            }else if (strlen($destlibsystem) <1) {
                $destlibsystemtxt = "All";
            }else{
                $destlibsystemtxt = "SENYLRC Group";
            }
            echo "<!-- \n";
            echo "catalogtype: $catalogtype \n";
            echo "itemavail: $itemavail (1) \n";
            echo "itemavailtext: $itemavailtext \n";
            echo "itemlocallocation: $itemlocallocation \n";
            echo "itemlocation: $itemlocation \n";
            echo "destill: $destill \n";
            echo "destpart: $destpart (1)\n";
            echo "destemail: $destemail \n";
            echo "destsuspend: $destsuspend (0)\n";
            echo "destlibsystem: $destlibsystem \n";
            echo "destlibname: $destlibname \n";
            echo "desttypeloan: $desttypeloan (1)\n";
            echo "failmessage: $failmessage\n";
            echo "--> \n\n";
            $destfail=0; // 0=No, 1=Yes
            if ($itemavail == 1) {
                $destfail = 1;
                $failmessage = "Material unavailable, see source ILS/LMS for details";
            }
            if ($destpart == 0) {
                $destfail = 1;
                $failmessage = "Library not particpating in CaDiLaC";
            }
            if (strlen($destemail) < 2) {
                $destfail = 1;
                $failmessage = "Library has no ILL email configured";
            }
            if ($destsuspend == 1) {
                $destfail = 2;
                $failmessage = "Library not loaning / closed";
            }
            if ($desttypeloan == 0) {
                $destfail = 2;
                $failmessage = "Library not loaning this material type";
            }

            if (strlen($destAlias) < 2) {
                $destfail = 1;
                $destlibname = $itemlocation;
                $destlibsystem = "Unknown";
                $failmessage = "No alias match in SEAL directory";
            }
            echo "<!-- \n";
            echo "destfail: $destfail\n";
            echo "--> \n\n";
            if ($destfail == 0) {
                $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
                $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
                $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
                echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                echo"<div class='singlereq'><input type='radio' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                $loccount=$loccount+1;
            } elseif ($destfail == 1) {
                //not showing fail code 1 to end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystemtxt), $failmessage</div>";
            } else {
                //will show other error to inform end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystemtxt), $failmessage</div>";
                echo "<!-- Holding location failed checks. --> \n";
            }
        }//end foreach loop for albany law school 994
    }//end if check for albany law school


    //want to add Koha locations to selection
    if (($catalogtype == "Koha")|| ($catalogtype == "Alexandria")) {
        // Pull the checksum for the location
        $seslcchecksum=$location['checksum'];
        // redo the curl statement to includes the checksum
        $cmdseslc= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$seslcchecksum\&offset=1";
        $outputseslc = shell_exec($cmdseslc);
        // This echo will show the CURL statment as an HTML comment
        // echo "\n<br><!-- my cmd koha is $cmdseslc \n-->";
        $recordssSESLC= new SimpleXMLElement($outputseslc); // for production
        // Go through the holding records
        foreach ($recordssSESLC->d952 as $d952) {
            //$itemavai=$d952['i1'];
            $itemlocation=$d952->sb;
            $itemcallnum=$d952->so;
            $itemavail=$d952->s7;
            // Remove colon from call numbers
            $seslccall= str_replace(':', '.', $seslccall);
            $itemavailtext=set_koha_availability($itemavail);
            $locationinfo=find_locationinfo($itemlocation, $location['name']);
            $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); // Sanitizes locations with special characters in them
            $destill=$locationinfo[0]; // Destination ILL Code
            $destpart=$locationinfo[1]; // 0=No, 1=Yes
            $destemail=$locationinfo[2]; // Destination emails
            $destsuspend=$locationinfo[3]; // 0=No, 1=Yes
            $destlibsystem=$locationinfo[4]; // Destination library system
            $destlibname=$locationinfo[5]; // Destination library name
            $destAlias=$locationinfo[6]; // Destination Alias
            $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); // Sanitizes library names with special characters in them
            $desttypeloan=check_itemtype($destill, $itemtype); // 0=No, 1=Yes
            $itemlocallocation=$itemlocation; // Needed in sent.php
             // translate system code to text name
             if (strcmp($destlibsystem, 'MH')==0) {
                $destlibsystemtxt = "Mid Hudson Library System";
            }else if (strcmp($destlibsystem, 'RC')==0) {
                $destlibsystemtxt = "Ramapo Catskill Library System";
            }else if (strcmp($destlibsystem, 'SE')==0) {
                $destlibsystemtxt = "SENYLRC";
            }else if (strcmp($destlibsystem, 'DU')==0) {
                $destlibsystemtxt = "Dutchess BOCES";
            }else if (strcmp($destlibsystem, 'OU')==0) {
                $destlibsystemtxt = "Orange Ulster BOCES";
            }else if (strcmp($destlibsystem, 'RB')==0) {
                $destlibsystemtxt = "Rockland BOCES";
            }else if (strcmp($destlibsystem, 'SB')==0) {
                $destlibsystemtxt = "Sullivan BOCES";
            }else if (strcmp($destlibsystem, 'UB')==0) {
                $destlibsystemtxt = "Ulster BOCES";
            }else if (strlen($destlibsystem) <1) {
                $destlibsystemtxt = "All";
            }else{
                $destlibsystemtxt = "SENYLRC Group";
            }
            echo "<!-- \n";
            echo "catalogtype: $catalogtype \n";
            echo "itemavail: $itemavail (1) \n";
            echo "itemavailtext: $itemavailtext \n";
            echo "itemlocallocation: $itemlocallocation \n";
            echo "itemlocation: $itemlocation \n";
            echo "destill: $destill \n";
            echo "destpart: $destpart (1)\n";
            echo "destemail: $destemail \n";
            echo "destsuspend: $destsuspend (0)\n";
            echo "destlibsystem: $destlibsystem \n";
            echo "destlibname: $destlibname \n";
            echo "desttypeloan: $desttypeloan (1)\n";
            echo "failmessage: $failmessage\n";
            echo "--> \n\n";
            $destfail=0; // 0=No, 1=Yes
            if ($destpart == 0) {
                $destfail = 1;
                $failmessage = "Library not particpating in CaDiLaC";
            }
            if ($itemavail == 1) {
                $destfail = 1;
                $failmessage = "Material unavailable, see source ILS/LMS for details";
            }
            if (strlen($destemail) < 2) {
                $destfail = 1;
                $failmessage = "Library has no ILL email configured";
            }
            if ($destsuspend == 1) {
                $destfail = 2;
                $failmessage = "Library not loaning / closed";
            }
            if ($desttypeloan == 0) {
                $destfail = 2;
                $failmessage = "Library not loaning this material type";
            }
            //  if (($destlibsystem == $field_home_library_system[0]['value']) && ($field_filter_own_system[0]['value'] == 1)) {
            //        $destfail = 1;
            //          $failmessage = "Library a member of your system, please request through your ILS/LMS";
            //      }
            if ($destAlias == "") {
                $destfail = 1;
                $destlibname = $itemlocation;
                $destlibsystem = "Unknown";
                $failmessage = "No alias match in SEAL directory";
            }
            echo "<!-- \n";
            echo "destfail: $destfail\n";
            echo "--> \n\n";
            if ($destfail == 0) {
                $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
                $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
                $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
                echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                echo"<div class='singlereq'><input type='radio' class='librarycheck[]' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystemtxt."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                $loccount=$loccount+1;
            } elseif ($destfail == 1) {
                //not showing fail code 1 to end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystemtxt), $failmessage</div>";
            } else {
                //will show other error to inform end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystemtxt), $failmessage</div>";
                echo "<!-- Holding location failed checks. --> \n";
            }
        }//end foreach $recordssSESLC
    }//end if cat type koha
} // End generic handler
echo "</select>";
foreach ($deadlibraries as $line) {
    echo $line;
}
if ($loccount > 0) {
    echo "<br><input type=Submit value=Submit> ";
    // If we have no locations don't show submit and display error
} else {
    echo "<br><b>Sorry, no available library to route your request at this time.</b>  <a href='/'>Would you like to try another search ?</a>";
}
echo "</form>";
?>
<script>
    function multiRequest() {
        var list = document.getElementsByClassName("librarycheck");
        for (var i = 0; i < list.length; i++) {
            list[i].checked = false;
        }
        if (document.getElementById('multiCheck').checked) {
            var list = document.getElementsByClassName("multiplereq");
            for (var i = 0; i < list.length; i++) {
                list[i].style.display = 'block';
            }
            var list = document.getElementsByClassName("singlereq");
            for (var i = 0; i < list.length; i++) {
                list[i].style.display = 'none';
            }
        } else {
            var list = document.getElementsByClassName("multiplereq");
            for (var i = 0; i < list.length; i++) {
                list[i].style.display = 'none';
            }
            var list = document.getElementsByClassName("singlereq");
            for (var i = 0; i < list.length; i++) {
                list[i].style.display = 'block';
            }
        }
    }

    // Call multiRequest when the page loads
    window.onload = function() {
        multiRequest();
    };
</script>

